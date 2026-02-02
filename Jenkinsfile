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
                '''
            }
        }

        stage('Plot Code Metrics') {
            steps {
                // Archive the CSV for debugging
                archiveArtifacts artifacts: 'build/logs/phploc.csv', fingerprint: true
                
                plot csvFileName: 'plot-loc.csv',
                     csvSeries: [[
                         displayTableFlag: false,
                         exclusionValues: 'Lines of Code (LOC),Comment Lines of Code (CLOC),Non-Comment Lines of Code (NCLOC),Logical Lines of Code (LLOC)',
                         file: 'build/logs/phploc.csv',
                         inclusionFlag: 'INCLUDE_BY_STRING',
                         url: ''
                     ]],
                     group: 'phploc',
                     numBuilds: '100',
                     style: 'line',
                     title: 'A - Lines of code',
                     yaxis: 'Lines of Code'

                plot csvFileName: 'plot-structures.csv',
                     csvSeries: [[
                         displayTableFlag: false,
                         exclusionValues: 'Directories,Files,Namespaces',
                         file: 'build/logs/phploc.csv',
                         inclusionFlag: 'INCLUDE_BY_STRING',
                         url: ''
                     ]],
                     group: 'phploc',
                     numBuilds: '100',
                     style: 'line',
                     title: 'B - Structures Containers',
                     yaxis: 'Count'

                plot csvFileName: 'plot-classes.csv',
                     csvSeries: [[
                         displayTableFlag: false,
                         exclusionValues: 'Classes,Abstract Classes,Concrete Classes',
                         file: 'build/logs/phploc.csv',
                         inclusionFlag: 'INCLUDE_BY_STRING',
                         url: ''
                     ]],
                     group: 'phploc',
                     numBuilds: '100',
                     style: 'line',
                     title: 'E - Types of Classes',
                     yaxis: 'Count'

                plot csvFileName: 'plot-methods.csv',
                     csvSeries: [[
                         displayTableFlag: false,
                         exclusionValues: 'Methods,Non-Static Methods,Static Methods,Public Methods,Non-Public Methods',
                         file: 'build/logs/phploc.csv',
                         inclusionFlag: 'INCLUDE_BY_STRING',
                         url: ''
                     ]],
                     group: 'phploc',
                     numBuilds: '100',
                     style: 'line',
                     title: 'F - Types of Methods',
                     yaxis: 'Count'

                plot csvFileName: 'plot-complexity.csv',
                     csvSeries: [[
                         displayTableFlag: false,
                         exclusionValues: 'Cyclomatic Complexity / Lines of Code,Cyclomatic Complexity / Number of Methods',
                         file: 'build/logs/phploc.csv',
                         inclusionFlag: 'INCLUDE_BY_STRING',
                         url: ''
                     ]],
                     group: 'phploc',
                     numBuilds: '100',
                     style: 'line',
                     title: 'D - Relative Cyclomatic Complexity',
                     yaxis: 'Cyclomatic Complexity by Structure'
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