<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Vision Alerts</title>

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav id="navbar" class="navbar has-shadow is-spaced">
            <div class="container">
                <div class="navbar-brand">
                    <a class="navbar-item" href="/">
                        <span class="heading is-size-4 mb-0">Vision Alerts</span>
                    </a>

                    <a role="button" class="navbar-burger burger" onclick="document.querySelector('.navbar-menu').classList.toggle('is-active');">
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </a>
                </div>

                <div class="navbar-menu">
                    <div class="navbar-start">
                        <router-link to="/profiles" class="navbar-item">
                            <span class="icon has-text-primary">
                                <i class="fas fa-eye"></i>
                            </span>
                            <span class="is-hidden-touch is-hidden-widescreen">
                                Profiles
                            </span>
                            <span class="is-hidden-desktop-only">
                                Detection Profiles
                            </span>
                        </router-link>

                        <router-link to="/events" class="navbar-item">
                            <span class="icon has-text-danger">
                                <i class="fas fa-images"></i>
                            </span>

                            <span>Detection Events</span>
                        </router-link>


                        <div class="navbar-item has-dropdown is-hoverable">

                            <router-link class="navbar-link" to="/automations">
                                Automation
                            </router-link>

                            <div id="moreDropdown" class="navbar-dropdown">
                                <router-link to="/automations/folderCopy" class="navbar-item">
                                    <span>
                                        <span class="icon has-text-info">
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <strong>Folder Copy</strong>
                                        <br>
                                        Copy image files to a local folder
                                  </span>
                                </router-link>

                                <hr class="navbar-divider ">

                                <router-link to="/automations/smbCifsCopy" class="navbar-item">
                                    <span>
                                        <span class="icon has-text-info">
                                            <i class="fas fa-upload"></i>
                                        </span>
                                        <strong>SMB/CIFS Copy</strong>
                                        <br>
                                        Upload images to Samba share
                                  </span>
                                </router-link>

                                <hr class="navbar-divider ">

                                <router-link to="/automations/telegram" class="navbar-item">
                                    <span>
                                        <span class="icon has-text-info">
                                            <i class="fab fa-telegram-plane"></i>
                                        </span>
                                        <strong>Telegram</strong>
                                        <br>
                                        Send images to Telegram bots
                                  </span>
                                </router-link>

                                <hr class="navbar-divider ">

                                <router-link to="/automations/webRequest" class="navbar-item">
                                    <span>
                                        <span class="icon has-text-info">
                                            <i class="fas fa-globe-americas"></i>
                                        </span>
                                        <strong>Web Request</strong>
                                        <br>
                                        Make Http GET requests
                                  </span>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <div class="container main-container">
                <div class="container lead">
                    <router-view></router-view>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ mix("js/app.js") }}" type="text/javascript"></script>
</body>
</html>
