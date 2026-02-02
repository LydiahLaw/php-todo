pipeline {
    agent any

    stages {

        stage('Initial cleanup') {
            steps {
                deleteDir()
            }
        }

        stage('Checkout SCM') {
            steps {
                git branch: 'main', url: 'https://github.com/LydiahLaw/php-todo.git'
            }
        }

        stage('Prepare Dependencies') {
            steps {
                sh '''
                cp .env.sample .env
                php artisan key:generate

                sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
                sed -i 's/DB_DATABASE=.*/DB_DATABASE=database\\/database.sqlite/' .env
                sed -i 's/DB_HOST=.*/DB_HOST=/' .env
                sed -i 's/DB_PORT=.*/DB_PORT=/' .env
                sed -i 's/DB_USERNAME=.*/DB_USERNAME=/' .env
                sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=/' .env

                composer install --no-interaction --prefer-dist

                # Ensure PHPLoc is installed
                composer require --dev phploc/phploc || true

                mkdir -p storage bootstrap/cache database build/logs
                touch database/database.sqlite

                chmod -R 775 storage bootstrap/cache database

                php artisan config:clear
                php artisan cache:clear || true
                '''
            }
        }

        stage('Database Setup') {
            steps {
                sh '''
                php artisan migrate --force
                php artisan db:seed --force
                '''
            }
        }

        stage('Execute Unit Tests') {
            steps {
                catchError(buildResult: 'SUCCESS', stageResult: 'UNSTABLE') {
                    sh 'php ./vendor/bin/phpunit'
                }
            }
        }

        stage('Code Analysis') {
            steps {
                sh '''
                # Ensure logs directory exists
                mkdir -p build/logs

                # Generate PHPLoc CSV
                ./vendor/bin/phploc app/ --log-csv build/logs/phploc.csv

                # Transform PHPLoc CSV into numeric CSV for Jenkins Plot
                # We'll extract these metrics: Lines of Code, Classes, Methods, Cyclomatic Complexity
                grep -E "Lines of Code|Classes|Methods|Cyclomatic Complexity" build/logs/phploc.csv \
                    | awk -F, '{print $2}' > build/logs/phploc_plot.csv
                '''
            }
        }

        stage('Plot Code Metrics') {
            steps {
                archiveArtifacts artifacts: 'build/logs/phploc_plot.csv', fingerprint: true

                plot csvFileName: 'plot-loc.csv',
                     csvSeries: [[file: 'build/logs/phploc_plot.csv', inclusionFlag: 'INCLUDE_BY_POSITION', displayTableFlag: false]],
                     group: 'PHP Metrics',
                     numBuilds: '100',
                     style: 'line',
                     title: 'Lines of Code'

                plot csvFileName: 'plot-classes.csv',
                     csvSeries: [[file: 'build/logs/phploc_plot.csv', inclusionFlag: 'INCLUDE_BY_POSITION', displayTableFlag: false]],
                     group: 'PHP Metrics',
                     numBuilds: '100',
                     style: 'line',
                     title: 'Classes'

                plot csvFileName: 'plot-methods.csv',
                     csvSeries: [[file: 'build/logs/phploc_plot.csv', inclusionFlag: 'INCLUDE_BY_POSITION', displayTableFlag: false]],
                     group: 'PHP Metrics',
                     numBuilds: '100',
                     style: 'line',
                     title: 'Methods'

                plot csvFileName: 'plot-complexity.csv',
                     csvSeries: [[file: 'build/logs/phploc_plot.csv', inclusionFlag: 'INCLUDE_BY_POSITION', displayTableFlag: false]],
                     group: 'PHP Metrics',
                     numBuilds: '100',
                     style: 'line',
                     title: 'Cyclomatic Complexity'
            }
        }

        stage('SonarCloud Analysis') {
            environment {
                scannerHome = tool 'SonarQubeScanner'
            }
            steps {
                withSonarQubeEnv('sonarqube') {
                    sh "${scannerHome}/bin/sonar-scanner -Dproject.settings=sonar-project.properties"
                }
            }
        }
    }
}
