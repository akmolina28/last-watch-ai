<template>
    <header class="lw-header mb-6 container" style="">
        <div class="header-titles">
            <div>
                <h1 class="title">
                    <slot name="title"></slot>
                </h1>
                <p class="subtitle is-4 has-text-grey-light">
                    <slot name="subtitle"></slot>
                </p>
                <div id="meta" class="field is-grouped is-grouped-multiline">
                    <slot name="meta"></slot>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
    export default {
        name: 'TitleHeader'
    }
</script>

<style scoped>

</style>
