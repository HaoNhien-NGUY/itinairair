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

## Usage

(Add instructions on how to use the application here)

**Turbo convention**:

```├── base.html.twig
├── components/           # Reusable UI components
│   ├── _card.html.twig
│   ├── _modal.html.twig
│   └── _notification.html.twig
│
├── [entity]/            # e.g., product/, user/, post/
│   ├── index.html.twig       # Full page
│   ├── show.html.twig        # Full page
│   ├── _form.html.twig       # Partial (reusable form)
│   ├── _list.html.twig       # Partial (list of items)
│   ├── _item.html.twig       # Partial (single item)
│   │
│   └── turbo/               # Turbo-specific templates
│       ├── _form_frame.html.twig
│       ├── _list_frame.html.twig
│       ├── create_stream.html.twig
│       ├── update_stream.html.twig
│       └── delete_stream.html.twig
│
└── layout/
├── _header.html.twig
├── _footer.html.twig
└── _sidebar.html.twig
```
