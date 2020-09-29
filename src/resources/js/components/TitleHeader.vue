<template>
    <header class="header mb-6" style="display: flex;">
        <div class="header-titles">
            <h1 class="title">
                <slot name="title"></slot>
            </h1>
            <p class="subtitle is-4 has-text-grey-light">
                <slot name="subtitle"></slot>
            </p>
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
