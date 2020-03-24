<template>
    <div class="page-admin-etc">
        <button class="btn btn_green" @click="updateSiteMap"><span>Обновить SiteMap</span></button>
        <button class="btn btn_green" @click="sendNews"><span>Разослать новости</span></button>
        <button class="btn btn_green" @click="clearCache"><span>Очистить кеш</span></button>
    </div>
</template>

<script>
    export default {
        name: "EtcComponent",
        data: function () {
            return {}
        },
        computed: {},
        methods: {
            updateSiteMap: function (e) {
                var $btn = $(e.target.parentElement);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.get('ajax_update_sitemap').then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');
                    
                }, response => {});
            },
            sendNews: function (e) {
                var $btn = $(e.target.parentElement);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.get('ajax_send_news').then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                }, response => {});
            },
            clearCache: function (e) {
                var $btn = $(e.target.parentElement);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.get('ajax_clear_all_cache').then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                }, response => {});
            }
        }
    }
</script>