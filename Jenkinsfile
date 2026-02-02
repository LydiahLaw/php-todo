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
                composer install --no-interaction --prefer-dist
                mkdir -p storage bootstrap/cache
                chmod -R 775 storage bootstrap/cache
                php artisan key:generate
                php artisan config:clear
                php artisan cache:clear || true
                '''
            }
        }

        stage('Database Setup') {
            steps {
                sh '''
                php artisan migrate:fresh --force
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
                sh 'phploc app/ --log-csv build/logs/phploc.csv'
            }
        }

        stage('Plot Code Coverage Report') {
            steps {

                plot csvFileName: 'phploc.csv',
                     csvSeries: [[file: 'build/logs/phploc.csv', inclusionFlag: 'INCLUDE_BY_STRING']],
                     group: 'phploc',
                     title: 'Lines of Code',
                     yaxis: 'LOC'

                plot csvFileName: 'phploc.csv',
                     csvSeries: [[file: 'build/logs/phploc.csv', inclusionFlag: 'INCLUDE_BY_STRING']],
                     group: 'phploc',
                     title: 'Structures',
                     yaxis: 'Count'

                plot csvFileName: 'phploc.csv',
                     csvSeries: [[file: 'build/logs/phploc.csv', inclusionFlag: 'INCLUDE_BY_STRING']],
                     group: 'phploc',
                     title: 'Cyclomatic Complexity',
                     yaxis: 'Complexity'
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
