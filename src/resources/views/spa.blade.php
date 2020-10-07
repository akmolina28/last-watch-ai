<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Last Watch AI</title>

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <main>

            <b-navbar wrapper-class="container" class="is-spaced has-shadow">
                        <template slot="brand">
                            <b-navbar-item tag="router-link" :to="{ path: '/' }">
                                <span class="heading is-size-4 mb-0">Last Watch</span>
                            </b-navbar-item>
                        </template>
                        <template slot="start">
                            <b-navbar-item href="/profiles">
                                <span class="icon has-text-primary">
                                    <b-icon icon="eye"></b-icon>
                                </span>
                                <span>
                                    Detection Profiles
                                </span>
                            </b-navbar-item>
                            <b-navbar-item href="/events">
                                <span class="icon has-text-primary">
                                    <b-icon icon="images"></b-icon>
                                </span>
                                <span>
                                    Detection Events
                                </span>
                            </b-navbar-item>


                            <b-dropdown aria-role="menu">
                                <a
                                    class="navbar-item"
                                    slot="trigger"
                                    role="button">
                                    <span>Automations</span>
                                    <b-icon icon="chevron-down"></b-icon>
                                </a>

                                <b-dropdown-item has-link aria-role="menuitem">
                                    <router-link to="/automations/folderCopy" class="dropdown-item">
                                        <span>
                                            <span class="icon has-text-primary">
                                                <b-icon icon="copy"></b-icon>
                                            </span>
                                            <strong>Folder Copy</strong>
                                            <br>
                                            Copy image files to a local folder
                                        </span>
                                    </router-link>
                                </b-dropdown-item>

                                <b-dropdown-item has-link aria-role="menuitem">
                                    <router-link to="/automations/smbCifsCopy" class="dropdown-item">
                                        <span>
                                            <span class="icon has-text-primary">
                                                <b-icon icon="upload"></b-icon>
                                            </span>
                                            <strong>SMB/CIFS Copy</strong>
                                            <br>
                                            Upload images to Samba share
                                        </span>
                                    </router-link>
                                </b-dropdown-item>

                                <b-dropdown-item has-link aria-role="menuitem">
                                    <router-link to="/automations/telegram" class="dropdown-item">
                                        <span>
                                            <span class="icon has-text-primary">
                                                <b-icon pack="fab" icon="telegram-plane"></b-icon>
                                            </span>
                                            <strong>Telegram</strong>
                                            <br>
                                            Send images to Telegram bots
                                        </span>
                                    </router-link>
                                </b-dropdown-item>

                                <b-dropdown-item has-link aria-role="menuitem">
                                    <router-link to="/automations/webRequest" class="dropdown-item">
                                        <span>
                                            <span class="icon has-text-primary">
                                                <b-icon icon="globe-americas"></b-icon>
                                            </span>
                                            <strong>Web Request</strong>
                                            <br>
                                            Make Http GET requests
                                        </span>
                                    </router-link>
                                </b-dropdown-item>
                            </b-dropdown>
                        </template>


                </b-navbar>

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
