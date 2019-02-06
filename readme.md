# ucreate Database Services ( UDS )

UDS is a platform to manage **PostgreSQL** services e.g ( manage users, database).

## About Project

- Language: PHP (7.3)
- Framework: Laravel ( >= 5.7 )
- Database: PostgreSQL

## [Server Requirements](https://github.com/parshant-ucreate/db_test/wiki/Server-Requirements)
 - 512 Mb RAM ( minimum )
 - [PHP](https://www.php.net) ( 7.3 )
 - [PostgreSQL](https://www.postgresql.org/docs/10/index.html)
 - [crontab](https://help.ubuntu.com/community/CronHowto)
 - [pgBadger](http://pgbadger.darold.net/#download)

## Setup
 - [How to install project on local](https://github.com/parshant-ucreate/db_test/wiki/Setup)

## External Services/API Reference

- **Images Storage**
    >
        - AWS S3
         1. Your S3 credentials can be found on the Security Credentials section of the AWS Acount
         2. Open the S3 section of the AWS Management Console and create a new bucket.
         3. Set AWS access key, secret key, bucket name etc. as environment variables.
        Reference: https://aws.amazon.com/s3
        
- **Google's Two Factor Authentication**
    >
        Two factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. 
        Two factor authentication protects against phishing, social engineering and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.
        
        Reference: 
         - https://scotch.io/tutorials/how-to-add-googles-two-factor-authentication-to-laravel
