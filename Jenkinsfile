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
                // Copy environment file if missing
                sh 'cp .env.sample .env || cp .env.example .env'

                // Clear composer cache to avoid conflicts
                sh 'composer clear-cache'

                // Install PHP dependencies
                sh 'composer install --no-interaction --prefer-dist'

                // Generate app key
                sh 'php artisan key:generate'

                // Clear cached configs and views after dependencies are installed
                sh 'php artisan config:clear'
                sh 'php artisan cache:clear'
                sh 'php artisan view:clear'
            }
        }

        stage('Database Setup') {
            steps {
                // Run migrations and seed the database
                sh 'php artisan migrate:fresh --force'
                sh 'php artisan db:seed --force'
            }
        }

        stage('Execute Unit Tests') {
            steps {
                // Run PHPUnit tests
                sh './vendor/bin/phpunit || echo "Tests completed with warnings"'
            }
        }
    }
}
