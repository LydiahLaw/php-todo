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
                mkdir -p build/logs
                ./vendor/bin/phploc app/ --log-csv build/logs/phploc.csv

                # Split PHPLoc CSV into separate metric files for Jenkins plots
                awk -F',' 'NR==1{next} {print "Metric,Value" > "build/logs/lines-of-code.csv"; print "Lines of Code,"$1 > "build/logs/lines-of-code.csv"}' build/logs/phploc.csv
                awk -F',' 'NR==1{next} {print "Metric,Value" > "build/logs/classes.csv"; print "Classes,"$2 > "build/logs/classes.csv"}' build/logs/phploc.csv
                awk -F',' 'NR==1{next} {print "Metric,Value" > "build/logs/methods.csv"; print "Methods,"$3 > "build/logs/methods.csv"}' build/logs/phploc.csv
                '''
            }
        }

        stage('Plot Code Metrics') {
            steps {
                plot csvFileName: 'lines-of-code.csv',
                     csvSeries: [[file: 'build/logs/lines-of-code.csv']],
                     group: 'PHP Metrics',
                     style: 'line',
                     title: 'PHP Lines of Code'

                plot csvFileName: 'classes.csv',
                     csvSeries: [[file: 'build/logs/classes.csv']],
                     group: 'PHP Metrics',
                     style: 'line',
                     title: 'PHP Classes'

                plot csvFileName: 'methods.csv',
                     csvSeries: [[file: 'build/logs/methods.csv']],
                     group: 'PHP Metrics',
                     style: 'line',
                     title: 'PHP Methods'
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
