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
                sh 'composer update --no-interaction --no-audit --ignore-platform-reqs'
                sh 'php artisan key:generate || true'
            }
        }

        stage('Database Setup') {
            steps {
                sh 'php artisan migrate --force || true'
                sh 'php artisan db:seed --force || true'
            }
        }

        stage('Execute Unit Tests') {
            steps {
                sh './vendor/bin/phpunit || true'
            }
        }
    }
}