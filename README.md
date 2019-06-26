# Like Tinder or Badoo Application

The main idea is to repeat general functionality of dating headliners like Badoo and Tinder

## Install the Application

1. Install dependencies:

        composer install

2. Install docker environment:
        
        docker-compose up --build -d

3. Install tables for database:
    
        http://ip-your-docker-machine:8080/src/setup.php

3. Application will be available on 8080 port of ip your docker machine

###Already done:

* Sign up, Login, Logout
* Users' profiles with additional information
* Tags of interests
* Uploading and deleting photos and avatars
* Likability of accounts
* Ability to look at all views and likes of your profile
 

###TODO:

* 'Smart' finder. Coefficients of like, geo location, etc.
* Chat with two-side-liked people
* Geo location
* Notification about likes and views

