How to Run This Project Locally

Step 1: Database Setup

1. Open phpMyAdmin.
2. Create a new database named `financial_logger`.
3. Import the `finance_logger.sql` file provided in this repository.

Step 2: Backend Setup (PHP)

1. Move the `backend` folder to your local server directory (e.g., `C:/xampp/htdocs/financial-logging-system/backend`).
2. Open `backend/db.php` and ensure the database credentials (username, password, dbname) match your local settings.

Step 3: Frontend Setup (Vue 3)

1. Open your terminal in the root folder of the project.
2. Install the necessary dependencies:
```bash
npm install

```


3. Start the development server:
```bash
npm run dev

```


4. Open the URL provided in the terminal (usually `http://localhost:5173`) to view the dashboard.



