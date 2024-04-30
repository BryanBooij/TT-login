# this a internship project

beware this project is mostly functionality only

# rights reserved for BryanBooij


# Databasse 
use the xampp build in database with you're own credentials

create a database called users and make the table: user
the database table needs the following: 

id (int 255, auto increment)

username (varchar 45)

display_username (varchar 45)

email (varchar 45, UNIQUE)

number (Varchar 45)

password (varchar 255)

secret (varchar 20)

qr_scanned (tinyint 1, default 0)

NOTE:
password needs to be 255 for hash length and secret needs to be at least 20 16 characters is used now but it can change to 20 in future update.