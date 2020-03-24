<template>
    <div class="page-admin-info">
        <table class="page-admin-tbl">
            <thead>
                <th>№</th>
                <th>Название</th>
                <th>Key</th>
            </thead>
            <tbody v-if="items.length">

            </tbody>
            <tbody v-else>
                <tr is="app-tr-empty" :colspan-amount="3"></tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: "InfoComponent",
        provide: function () {
            return {}
        },
        data: function () {
            return {
                items: []
            }
        },
        components: {},
        computed: {},
        methods: {}
    }
</script>