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
                git(
                    branch: 'main',
                    url: 'https://github.com/LydiahLaw/php-todo.git',
                    credentialsId: 'php-todo-github'
                )
            }
        }

        stage('Prepare Environment') {
            steps {
                sh 'cp .env.sample .env'
                sh 'composer install --no-interaction --prefer-dist'
                sh 'php artisan key:generate'
                sh 'php artisan config:clear'
                sh 'php artisan cache:clear'
            }
        }

        stage('Database Setup') {
            steps {
                sh 'php artisan migrate --force'
                sh 'php artisan db:seed --force'
            }
        }

        stage('Execute Unit Tests') {
            steps {
                sh './vendor/bin/phpunit'
            }
        }
    }
}
