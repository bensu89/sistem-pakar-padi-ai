# Rice Disease Detection (Padi Web) 🌾

A Laravel-based web application designed to detect diseases in rice plants through image analysis. This project helps farmers and researchers to quickly diagnose plant health issues and monitor disease spread.

## 🚀 Features

- **Disease Diagnosis**: Upload or capture images of rice plants for instant disease analysis.
- **AI Integration**: Communicates with an AI/ML service for image processing and prediction.
- **Admin Dashboard**: Comprehensive monitoring dashboard at `/monitoring-penelitian` for administrators to view recent scans and reports.
- **Report Management**:
  - View detailed diagnosis history.
  - Delete valid or invalid (junk) reports.
  - Export reports to Excel/CSV for offline analysis.
- **Secure Authentication**: Built-in authentication for administrative access.

## 🛠️ Tech Stack

- **Backend framework**: Laravel 8.x
- **Language**: PHP ^7.3|^8.0
- **Database**: MySQL (Eloquent ORM)
- **Frontend**: Blade Templates, Bootstrap (Standard Laravel UI)
- **HTTP Client**: Guzzle (for external API communication)
- **API Authentication**: Laravel Sanctum

## ⚙️ Installation

Follow these steps to set up the project locally:

1.  **Clone the repository**
    ```bash
    git clone https://github.com/yourusername/padi-web.git
    cd padi-web
    ```

2.  **Install PHP dependencies**
    ```bash
    composer install
    ```

3.  **Environment Configuration**
    Copy the example environment file and configure your database settings:
    ```bash
    cp .env.example .env
    ```
    Update the `.env` file with your database credentials:
    ```env
    DB_DATABASE=padi_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations**
    Set up the database tables:
    ```bash
    php artisan migrate
    ```

6.  **Serve the Application**
    Start the local development server:
    ```bash
    php artisan serve
    ```
    The application will be accessible at `http://localhost:8000`.

## 📂 Key Routes

- **Home (Diagnosis)**: `/`
- **Admin Dashboard**: `/monitoring-penelitian`
- **Export Report**: `/export-laporan`

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
