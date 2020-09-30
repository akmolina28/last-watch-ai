# Last Watch AI

![preview1](previews/detection-event2.JPG)

Last Watch AI is a standalone tool for creating if-then automations based on computer vision events. A primary use for this tool is the automation of motion events from NVR software such as Blue Iris. This project was heavily inspired by [gentlepumpkin/bi-aidetection](https://github.com/gentlepumpkin/bi-aidetection).

## How it works

Last Watch watches for new image files and uses AI to check each image for a range of objects such as cars or people. If a relevant object is detected, automations are triggered.

The most common use case is for NVR home security systems. For example, Last Watch can be set up to send Telegram messages when a person is detected on a camera.

Last Watch runs completely "offline". All processing is handled localling using [DeepStack AI Server](https://deepstack.cc/). Last Watch is also platform independent thanks to containerization using Docker.

## Features

* Platform Independence - everything runs in Docker containers
* Extensibility - designed to be forked and added to
* Web-based Interface - desktop and mobile friendly
* 100% locally hosted

Supported Automations:

* Telegram - send images to bot
* Folder Copy - copy images to a local folder
* Smb/Cifs - upload images to a Samba share (Home Assistant, Synology)
* Web Request - make http requests

## Installation

** install the latest stable version of Docker first **

1. Download the latest release zip and extract the files

2. Edit .env file at the root level and set the watch folder location as desired

3. Run docker-compose command to start the containers: `docker-compose up -d --build site`

## Upgrading

1. Download the latest release zip and extract the files

2. Stop all existing containers: `docker-compose down`

3. Copy mysql folder from previous install into the new install folder

4. Run migrations: `docker-compose run --rm artisan migrate`

5. Start the containers: `docker-compose up -d --build site`

## Building from source

1. git clone https://github.com/akmolina28/last-watch-ai.git

2. cd last-watch-ai

3. cp src/.env.example src/.env

4. docker-compose run --rm composer install

5. docker-compose run --rm artisan key:generate

7. docker-compose run --rm artisan storage:link

8. docker-compose run --rm artisan migrate

9. docker-compose run --rm npm install

11. docker-compose run --rm npm run watch-poll
