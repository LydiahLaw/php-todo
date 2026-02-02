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

                sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
                sed -i 's/DB_DATABASE=.*/DB_DATABASE=database\\/database.sqlite/' .env
                sed -i 's/DB_HOST=.*/DB_HOST=/' .env
                sed -i 's/DB_PORT=.*/DB_PORT=/' .env
                sed -i 's/DB_USERNAME=.*/DB_USERNAME=/' .env
                sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=/' .env

                composer install --no-interaction --prefer-dist

                mkdir -p storage bootstrap/cache database
                touch database/database.sqlite

                chmod -R 775 storage bootstrap/cache database

                php artisan key:generate
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
                sh 'phploc app/ --log-csv build/logs/phploc.csv'
            }
        }

        stage('Plot Code Coverage Report') {
            steps {
                plot csvFileName: 'phploc.csv',
                     csvSeries: [
                         [file: 'build/logs/phploc.csv', label: 'Lines of Code']
                     ],
                     group: 'PHP Metrics',
                     style: 'line',
                     title: 'PHP Lines of Code'

                plot csvFileName: 'phploc.csv',
                     csvSeries: [
                         [file: 'build/logs/phploc.csv', label: 'Classes']
                     ],
                     group: 'PHP Metrics',
                     style: 'line',
                     title: 'PHP Classes'

                plot csvFileName: 'phploc.csv',
                     csvSeries: [
                         [file: 'build/logs/phploc.csv', label: 'Methods']
                     ],
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
