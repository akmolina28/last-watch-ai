<template>
    <div class="component-container">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/automations">Automations</a></li>
                <li class="is-active"><a href="#" aria-current="page">Mqtt Publish</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Mqtt Publish
            </template>
            <template v-slot:subtitle>
                Send mqtt messages
            </template>
        </title-header>

        <div class="responsive-table-wrapper mb-3">
            <table class="table pb-5">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Server</th>
                        <th>Port</th>
                        <th>Topic</th>
                        <th>Client ID</th>
                        <th>QoS</th>
                        <th>Anonymous</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Custom Payload</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="mqttPublishConfigs.length === 0" class="py-4 is-italic">
                        No configs set up yet. Add one below.
                    </tr>
                    <tr v-for="config in mqttPublishConfigs">
                        <td>{{ config.name }}</td>
                        <td>{{ config.server }}</td>
                        <td>{{ config.port }}</td>
                        <td>{{ config.topic }}</td>
                        <td>{{ config.client_id }}</td>
                        <td>{{ config.qos }}</td>
                        <td>
                            <b-icon v-if="config.is_anonymous" icon="check"></b-icon>
                        </td>
                        <td>{{ config.username }}</td>
                        <td><span v-if="config.password">********</span></td>
                        <td>
                            <span v-if="config.is_custom_payload">{{ config.payload_json | truncate }}</span>
                        </td>
                        <td>
                            <span :title="config.body_json">{{ config.body_json | truncate }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box is-6">
            <p class="subtitle">New Config</p>

            <form @submit.prevent="processForm">
                <b-field label="Name">
                    <b-input v-model="name" placeholder="Unique name" name="name" required></b-input>
                </b-field>

<!--                <div class="px-5 py-5 mb-3 has-background-light">-->
<!--                    <h5 class="title is-size-5">Substitution Variables</h5>-->
<!--                    <ul>-->
<!--                        <li>-->
<!--                            <strong>%image_file_name%</strong> - the event image file-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <strong>%profile_name%</strong> - the triggered profile name-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <strong>%object_classes%</strong> - object(s) which triggered the profile-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </div>-->

                <b-field label="Server">
                    <b-input v-model="server" placeholder="127.0.0.1" name="server" required></b-input>
                </b-field>

                <b-field label="Port">
                    <b-input v-model="port" placeholder="1883" name="port" required></b-input>
                </b-field>

                <b-field label="Topic">
                    <b-input v-model="topic" placeholder="mqtt/lastwatch/alert" name="topic" required></b-input>
                </b-field>

                <b-field label="Client ID">
                    <b-input v-model="clientId" placeholder="lastwatch" name="clientId"></b-input>
                </b-field>

                <b-field label="QoS">
                    <b-select v-model="qos" name="qos">
                        <option>0</option>
                        <option>1</option>
                        <option>2</option>
                    </b-select>
                </b-field>

                <b-field label="Anonymous">
                    <b-switch v-model="isAnonymous" />
                </b-field>

                <b-field label="Username">
                    <b-input v-model="username" name="username" :disabled="isAnonymous"></b-input>
                </b-field>

                <b-field label="Password">
                    <b-input v-model="password" name="password" :disabled="isAnonymous"></b-input>
                </b-field>

                <b-field label="Custom Payload">
                    <b-switch v-model="isCustomPayload" />
                </b-field>

                <b-field label="Payload (json)">
                    <b-input v-model="payloadJson"
                             name="json"
                             required
                             type="textarea"
                             :disabled="!isCustomPayload"></b-input>
                </b-field>

                <b-button type="is-primary"
                          :loading="isSaving"
                          icon-left="save"
                          native-type="submit">
                    Save Profile
                </b-button>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        name: "MqttPublishConfigs",

        data() {
            return {
                mqttPublishConfigs: [],
                name: '',
                server: '',
                port: '',
                topic: '',
                clientId: '',
                qos: 0,
                isAnonymous: false,
                username: '',
                password: '',
                payloadJson: '',
                isCustomPayload: false,
                isSaving: false
            }
        },

        mounted() {
            this.getData();
        },

        filters: {
            truncate(value) {
                let val = value ?? '';
                let ret = val.substring(0, 19);
                if (val.length > 19) {
                    ret += '...';
                }
                return ret;
            }
        },

        methods: {
            getData() {
                axios.get('/api/automations/mqttPublish').then(response => {
                    this.mqttPublishConfigs  = response.data.data;
                });
            },

            changePost() {
                this.bodyJson = '';
            },

            processForm: function () {
                let formData = {
                    'name': this.name,
                    'server': this.server,
                    'port': this.port,
                    'topic': this.topic,
                    'client_id': this.clientId,
                    'qos': this.qos,
                    'is_anonymous': this.isAnonymous,
                    'username': this.username,
                    'password': this.password,
                    'is_custom_payload': this.isCustomPayload,
                    'payload_json': this.payloadJson
                }

                this.isSaving = true;
                axios.post('/api/automations/mqttPublish', formData)
                    .then(response => {
                        this.mqttPublishConfigs.push(response.data.data);
                        this.name = '';
                        this.server = '';
                        this.port = '';
                        this.topic = '';
                        this.clientId = '';
                        this.qos = 0;
                        this.isAnonymous = false;
                        this.username = '';
                        this.password = '';
                        this.isCustomPayload = false;
                        this.payloadJson = '';
                    })
                    .catch(err => {
                        console.log(err.response);
                    })
                    .finally(() => {
                        this.isSaving = false;
                    })
            },
        }
    }
</script>

<style scoped>

</style>
