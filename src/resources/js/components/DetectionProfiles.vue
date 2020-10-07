<template>
    <div class="component-wrapper">
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="/">Home</a></li>
                <li class="is-active"><a href="#" aria-current="page">Detection Profiles</a></li>
            </ul>
        </nav>
        <title-header>
            <template v-slot:title>
                Detection Profiles
            </template>
            <template v-slot:subtitle>
                Configure the AI to run when certain image files are created
            </template>
        </title-header>

        <div class="mb-3">
            <a class="button is-info" href="/profiles/create">New Profile</a>
        </div>

        <div class="responsive-table-wrapper">
            <table class="table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Pattern</th>
                    <th>Regex</th>
                    <th>Object Classes</th>
                    <th>Min Confidence</th>
                    <th>Automations</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                    <tr v-for="profile in profiles" :key="profile.id">
                        <td>{{ profile.name }}</td>
                        <td>{{ profile.file_pattern }}</td>
                        <td>{{ profile.use_regex }}</td>
                        <td>{{ profile.object_classes.join('|') }}</td>
                        <td>{{ profile.min_confidence }}</td>
                        <td><router-link :to="`/profiles/${profile.id}/automations`">Automations</router-link></td>
                        <td>
                            <b-dropdown aria-role="list" :ref="'dropdown' + profile.id">
                                <button :class="'button is-primary' + (profile.status === 'disabled' ? ' is-light': '') + (profile.isStatusUpdating ? ' is-loading' : '')"
                                        slot="trigger" slot-scope="{ statusDropdownActive }">
                                    <span>{{ profile.status | menuize }}</span>
                                    <b-icon :icon="statusDropdownActive ? 'chevron-up' : 'chevron-down'"></b-icon>
                                </button>

                                <b-dropdown-item aria-role="listitem" @click="updateStatus(profile, 'enabled')">
                                    <span class="icon has-text-success">
                                        <b-icon icon="circle"></b-icon>
                                    </span>
                                    Enabled
                                </b-dropdown-item>
                                <hr class="dropdown-divider">
                                <b-dropdown-item aria-role="listitem" @click="updateStatus(profile, 'disabled')">
                                    <span class="icon has-text-grey">
                                        <b-icon icon="ban"></b-icon>
                                    </span>
                                    Disabled
                                </b-dropdown-item>
                                <hr class="dropdown-divider">
                                <b-dropdown-item aria-role="listitem" @click="showSchedulerModal(profile)">
                                    <span class="icon has-text-primary">
                                        <b-icon icon="clock"></b-icon>
                                    </span>
                                    As Scheduled
                                </b-dropdown-item>
                            </b-dropdown>

                        </td>
                        <td>
                            <button @click="deleteProfile(profile)" :class="'button is-danger is-outlined' + (profile.isDeleting ? ' is-loading' : '')">
                                <span class="icon is-small">
                                    <b-icon icon="trash"></b-icon>
                                </span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <pagination :data="meta" @pagination-change-page="getData"></pagination>

        </div>
        <b-modal
            v-model="isModalActive"
            trap-focus
            has-modal-card
            :destroy-on-hide="false"
            aria-role="dialog"
            aria-modal>
            <template #default="props">
                <scheduler-modal
                    v-bind="modalProps"
                    @close="props.close"
                    @save="saveSchedule">
                </scheduler-modal>
            </template>
        </b-modal>
    </div>
</template>

<script>
    const SchedulerModal = {
        props: ['profile'],
        template: `
            <form action="">
                <div class="modal-card" style="width: auto">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Set Schedule</p>
                        <button
                            type="button"
                            class="delete"
                            @click="$emit('close')"/>
                    </header>
                    <section class="modal-card-body">
                        <h5 class="title is-size-5">{{ profile.name }}</h5>
                        <b-field label="Start Time">
                            <b-timepicker
                                inline
                                v-model="profile.startTime"
                                hour-format="24">
                            </b-timepicker>
                        </b-field>

                        <b-field label="End Time" class="mb-10">
                            <b-timepicker
                                inline
                                v-model="profile.endTime"
                                hour-format="24">
                            </b-timepicker>
                        </b-field>

                    </section>
                    <footer class="modal-card-foot">
                        <button class="button" type="button" @click="$emit('close')">Close</button>
                        <button class="button is-primary" @click.prevent="save()">Save</button>
                    </footer>
                </div>
            </form>
        `,
        methods: {
            save() {
                this.$emit('save', this.profile);
                this.$emit('close');
            }
        }
    }
    export default {
        name: "DetectionProfiles",
        components: {
            SchedulerModal
        },
        data() {
            return {
                profiles: [],
                meta: {},
                isModalActive: false,
                modalProps: {
                    profile: {}
                }
            }
        },

        mounted () {
            this.getData();
        },

        filters: {
            menuize(value) {
                if (value === 'enabled') return 'Enabled';
                if (value === 'disabled') return 'Disabled';
                if (value === 'as_scheduled') return 'As Scheduled';
                return value;
            }
        },

        methods: {

            showSchedulerModal(profile) {
                this.modalProps.profile = profile;
                this.isModalActive = true;
            },

            getEndTime(profile) {
                if (profile.start_time) {
                    return Date.parse('2020-01-01 ' + profile.start_time);
                }
                return null;
            },

            statusButtonClass(profile) {
                let c = 'button';

                if (profile.status === 'active') {
                    c += ' is-primary';
                }

                return c;
            },

            getData(page = 1) {
                axios.get(`/api/profiles?page=${page}`)
                    .then(response => {
                        let profiles = response.data.data;
                        profiles.forEach(p => p.isDeleting = false);
                        profiles.forEach(p => p.isStatusUpdating = false);
                        profiles.forEach(p => p.startTime = (p.start_time ? new Date('2020-01-01 ' + p.start_time) : null));
                        profiles.forEach(p => p.endTime = (p.end_time ? new Date('2020-01-01 ' + p.end_time) : null));
                        this.profiles = profiles;
                        this.meta = response.data.meta;
                    });
            },

            deleteProfile(profile) {
                profile.isDeleting = true;
                axios.delete(`/api/profiles/${profile.id}`)
                    .then(() => {
                        this.profiles = this.profiles.filter(p => p.id !== profile.id);
                    })
                    .catch(() => {
                        profile.isDeleting = false;
                    });
            },

            getTime(date) {
                if (!date) return '';

                let hour = date.getHours();
                let hourStr = hour < 10 ? "0" + hour : "" + hour;

                let minute = date.getMinutes();
                let minStr = minute < 10 ? "0" + minute : "" + minute;

                return hourStr + ":" + minStr;
            },

            saveSchedule(profile) {
                this.updateStatus(profile, 'as_scheduled');
            },

            updateStatus(profile, status) {
                profile.isStatusUpdating = true;

                axios.put(`/api/profiles/${profile.id}/status`, {
                        'status': status,
                        'start_time': this.getTime(profile.startTime),
                        'end_time': this.getTime(profile.endTime)
                    })
                    .then(response => {
                        profile.isStatusUpdating = false;
                        profile.status = status;
                    })
                    .catch(() => {
                        profile.isStatusUpdating = false;
                    });
            }
        }
    }
</script>

<style scoped>
    .modal .animation-content .modal-card {
        overflow: visible !important;
    }

    .modal-card-body {
        overflow: visible !important;
    }
</style>
