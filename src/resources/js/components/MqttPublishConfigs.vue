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

        <b-table :data="mqttPublishConfigs">
            <template #empty>
                No automations set up yet. Use the form below.
            </template>
            <b-table-column field="name" label="Name" v-slot="props">
                {{ props.row.name }}
            </b-table-column>
            <b-table-column field="server" label="Server" v-slot="props">
                {{ props.row.server }}
            </b-table-column>
            <b-table-column field="port" label="Port" v-slot="props">
                {{ props.row.port }}
            </b-table-column>
            <b-table-column field="topic" label="Topic" v-slot="props">
                {{ props.row.topic }}
            </b-table-column>
            <b-table-column field="client_id" label="Client ID" v-slot="props">
                {{ props.row.client_id }}
            </b-table-column>
            <b-table-column field="qos" label="QoS ID" v-slot="props">
                {{ props.row.qos }}
            </b-table-column>
            <b-table-column field="profile_count" label="Used By" v-slot="props">
                {{ props.row.detection_profiles.length }} profile(s)
            </b-table-column>
            <b-table-column field="delete" label="Delete" v-slot="props">
                <b-button icon-right="trash"
                          type="is-danger is-outlined"
                          :loading="props.row.isDeleting"
                          @click="deleteClick(props.row)">
                </b-button>
            </b-table-column>
        </b-table>

        <div class="box is-6">
            <p class="subtitle">New Config</p>

            <form @submit.prevent="processForm">
                <b-field label="Name">
                    <b-input v-model="name" placeholder="Unique name" name="name" required></b-input>
                </b-field>

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
                    let configs = response.data.data;
                    configs.forEach(c => c.isDeleting = false);
                    this.mqttPublishConfigs  = configs;
                });
            },

            changePost() {
                this.bodyJson = '';
            },

            deleteClick(config) {
                if (config.detection_profiles.length > 0) {
                    this.$buefy.dialog.confirm({
                        title: 'Confirm Delete',
                        message:
                            `Deleting this automation will automatically unsubscribe ${config.detection_profiles.length} Detection Profile(s).`,
                        confirmText: 'Delete',
                        type: 'is-danger',
                        hasIcon: true,
                        onConfirm: () => this.delete(config)
                    });
                }
                else {
                    this.delete(config);
                }
            },

            delete(config) {
                config.isDeleting = true;

                axios.delete(`/api/automations/mqttPublish/${config.id}`)
                    .then(() => {
                        this.mqttPublishConfigs = this.mqttPublishConfigs.filter(c => {
                            return c.id !== config.id;
                        });
                    })
                    .catch(() => {
                        config.deleting = false;
                        this.$buefy.toast.open({
                            message: 'Something went wrong. Refresh and try again.',
                            type: 'is-danger'
                        });
                    });
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
