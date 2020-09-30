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
