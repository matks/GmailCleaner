GmailCleaner
============

Script to clean a flooded gmail mailbox

## Background

In order to empty a flooded gmail box, I needed a script that would
connect to Google Gmail API and trigger targets-based deletions.

The best tool for the job would be a server-to-server script but I did not succeeded
to use the server-to-server Gmail API. An issue with an ssl-related lib.

So I made a in-browser web app for that and it works quite cool.

## Install

### Register GmailCleaner project to allow Google API OAuth

Go on https://console.developers.google.com

Create a new project with the name "GmailCleaner"

In "API > API" panel enable the Gmail API

In "API > credentials" panel, create a OAuth Key for web application. It will require
a loading screen configuration, fill it.

__Important__: configure a redirect URL, for example "http://fr.gmail.clean.com/clean.php"

### Get your Google API Key

Download the json key file.

Copy it in `config/auth/`.

### Install in-browser GmailCleaner app

Install dependencies with composer
```bash
$ php composer.phar install
```

Create a parameters.yml file with your Google API key

```bash
$ cp config/parameters.yml dist config/parameter.yml
```

Fill in the following parameters:
  * google_api_key: the name of your json file
  * from: lower time limit for the messages to delete selection
  * to: upper time limit for the messages to delete selection

Setup a web server to allow local browser usage.

## Usage

Load the web page clean.php using the path you defined (ex: localhost:80/clean.php), you should see:

<img src="https://cloud.githubusercontent.com/assets/3830050/9097177/3d81daea-3bc2-11e5-81ff-0c45c88ec23b.png"></img>

Click on "connect", select your gmail account and the script will delete 1000
gmail messages which match the given time limits.
