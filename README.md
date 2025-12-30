# Superadmin

### Site https://superadmin.engagereward.com

This guide explains how to install and run the Laravel project on both **CloudPanel** and a **local development environment**.

---

## ✅ Requirements

### General Requirements

1. A server with public IP and CloudPanel 2.0 installed.
2. SSH and SFTP/File Manager access.
3. Registered domain name.
4. GitHub repositories for:
    - engagereward-superadmin
5. Tech stack:
    - PHP 8.2+ (as required by your Laravel version)
    - Composer 2.x
    - MySQL 5.7+ or MariaDB 10.4+
    - Git
    - Ubuntu Server
    - Nginx (managed by CloudPanel)

---

## 🚀 Installation on CloudPanel

CloudPanel is optimized for PHP applications and provides an easy deployment workflow.

"https://www.cloudpanel.io/docs/v2/introduction/"

Side-by-Side create a mailing account from sites like Google, GoDaddy, Zoho, Sendgrid etc, from which system shall send mails. Note the outgoing details like host, port, username, password etc

Make sure that if you change the password from the mail account in the websites like google, godaddy, sendgrid, zoho etc, then you need to chanage the password in the .env files as well or for the saas panels too becasuse the authentication is done by those email service providers

### Deploy the Application

This is the documentation made for CloudPanel only, there are various panels like cPanel, OviPanel, Hostinger, Plesk etc, which are different and the process shall vary for each of these panels.

For details documentation on laravel site setups on the cloud panel, visit "https://www.cloudpanel.io/docs/v2/php/applications/laravel/". Just donot create a new project as we already have one

#### \*\*\*\*

Note: To make the both panels work, do the following things

1. Setup and Install Superadmin
2. Setup and Install SaaS Company Panel
3. You are all set and ready

#### \*\*\*\*

### 1. The MySQL USER ACCOUNT : CloudPanel Server Configuration ( if an account is already created in the Superadmin setup then ignore and use the same username password or create a new one using following steps). This is essential for Superamdin and Company SaaS Panel to work seemlessly

-   Note : These login username, password will be different than site user and also different than a systems root user. For security purposes we are creating a seperate user so that we could, restore/backup/stop anyone accessing form it and will be used as secondary

-   Step 1: Connect via SSH
    Using terminal:
    ssh username@your_server_ip

-   Step 2: MySQL Database Configuration

1. Retrieve MySQL root credentials, CloudPanel stores MySQL credentials in root level so enter following command to see them

    clpctl db:show:master-credentials

    Take note of:
    user=root
    password=XXXX

2. Log in to MySQL as root
   mysql -h'127.0.0.1' -P'3306' -u'<root_user>' -p'<password>' -A

3. Create a new MySQL user
   CREATE USER '<new_user_name>'@'%' IDENTIFIED BY 'StrongPasswordHere';
   Record the username and password.

4. Grant full privileges
   GRANT ALL PRIVILEGES ON _._ TO '<new_user_name>'@'%' WITH GRANT OPTION;
   FLUSH PRIVILEGES;

5. Exit MySQL
   EXIT;

6. Verify the new MySQL user has the right permission by executing following commands one by one.

-   Connect to MySQL By
    mysql -u newuser -p

-   Test database creation for permissions:

    CREATE DATABASE test_db; -- creates the database

    SHOW DATABASES; -- this shows all databases created by the current user

    DROP DATABASE test_db; -- drops the database

    EXIT; -- exit from the mysql user session

If you fail any where while setting up the User, PRIVILEGES, etc please checkout the offical documentation for mysql. https://dev.mysql.com/doc/

### 2. CloudPanel Site Configuration

-   Step 1: CloudPanel Site & DB Setup for Super Admin

-   Create the Super Admin site
-   Log into CloudPanel: https://your_server_ip:8443
-   Go to Sites → Create Site → Create PHP Site.
-   Enter:
    _ Domain: superadmin.yourdomain.com
    _ Type: PHP \* Template: Laravel 11 / Laravel 12 (Basic Setup)
    Take note of:

-   System user
-   System password
-   Site path

2. Create database for Super Admin
   CloudPanel → Your Site -> Manage-> Databases → Create Database. Fill the details and submit the form, make sure to note down the following things

-   Database Name
-   Database User
-   Database Password

3. Create SSL Certificate
   CloudPanel → Site → SSL → Let’s Encrypt → Create Certificate

-   Step 2: Deploy EngageReward Super Admin App

1. SSH into server & navigate to site directory
   ssh username@your_server_ip
   cd /home/<site-user>/htdocs/<your_domain>/

2. Pull the repository or upload ZIP
   Option A — Git clone:
   git clone https://github.com/your-org/engagereward-superadmin.git

    Option B — CloudPanel File Manager: Upload ZIP
    Extract inside the same directory

3. Install PHP dependencies
   composer install

4. Update the .env file
   Edit via SSH: "nano .env" Or via CloudPanel File Manager. These values control your application name, environment, URL, and debugging mode. Change only those values
   provided below and rest will remaining as it is. The env file in format of "key=value" pair, so you need to change the values only.

    - APP settings
      APP_NAME="<your_company_name>"
      APP_ENV=production (Enter "local" when require for development mode else "production")
      APP_DEBUG=true (Enter "true" when required to debug or see the errors else "false")
      APP_URL=<your superadmin url> e.g. https://<your-superadmin-domain>.<domain>.com
    - Support Email
      SUPPORT_MAIL=<your support email>

    - Database Username and password (created while creating the site in the above steps from the cloud panel)
      DB_DATABASE=<database_name>
      DB_USERNAME=<database_username>
      DB_PASSWORD=<database_password>

    - Email Settings (from the provider like google, godaddy, sendgrid or any of your wish). IF the password contains special characters then add them in double quotes to make it work
      MAIL_MAILER=smtp
      MAIL_HOST=smtp.gmail.com
      MAIL_PORT=587 or 25 or 465 etc
      MAIL_USERNAME=admin@engagereward.com
      MAIL_PASSWORD="<mail_password>"
      MAIL_ENCRYPTION=tls or ssl or STARTTLS
      MAIL_FROM_ADDRESS="<from_email_addres>"

    ##### For this step, please follow the root mysql database & Instance site root url as creation step mentioned in the Company Instance SaaS Panel".

    ---- Otherwise the setup wont work. You can change the settings after you setup the instance saas panel for better results ----

    - SaaS instance database username & password
      INS_DB_USER=<mysql-db-username-created-by-root-ssh-login>
      INS_DB_PASSWORD=<mysql-db-passowrd-created-by-root-ssh-login>

    - Instance site root url as
      ADMIN_API_URL=https://<your-instance-site-url>.<your-domain>.com/

    - Site User
      ADMIN_SITE_USER="<site-user-name>"

5. Run Laravel setup commands

-   php artisan cache:clear
-   php artisan config:clear
-   php artisan route:clear
-   php artisan view:clear
-   php artisan migrate
-   php artisan db:seed
-   php artisan storage:link

### 3. Now you're all set! and can use the superadmin panel

-   Now visit your superadmin panel by the site url created above, when login page appears use the following creadentials to login in the system

User 1
Username: superamdin@engagereward.com
Password: password@123

User 2
Username: admin@engagereward.com
Password: password@123

## Note - when ever you make changes in the .env file do the following steps to make the application work with new configurations

Rebuild Laravel caches by using the ssh login and visiting the projects root directory

-   php artisan config:clear
-   php artisan cache:clear
-   php artisan optimize
