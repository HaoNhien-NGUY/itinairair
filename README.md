# My Travel Project

## Description

This is a Symfony application for managing travel plans, including trips, accommodations, activities, and flights.

## Setup

1.  **Clone the repository**:

    ```bash
    git clone <repository_url>
    cd my_travel
    ```

2.  **Install Composer dependencies**:

    ```bash
    composer install
    ```

3.  **Install Node.js dependencies**:

    ```bash
    npm install
    ```

4.  **Set up environment variables**:

    Copy `.env` to `.env.local` and configure your database connection and other settings.

    ```bash
    cp .env .env.local
    ```

5.  **Run database migrations**:

    ```bash
    php bin/console doctrine:migrations:migrate
    ```

6.  **Start the Symfony development server**:

    ```bash
    symfony serve
    ```

    Or, if using Docker Compose:

    ```bash
    docker compose up -d
    ```

## Usage

(Add instructions on how to use the application here)
