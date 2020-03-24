<template>
    <div class="page-admin">
        <div class="page-admin__header">
            <a class="page-admin__logo" href="/"></a>

            <ul class="page-admin__top-menu">
                <li v-for="item in aTopMenuItems">
                    <router-link :to="item.href">{{ item.name }}</router-link>
                </li>
            </ul>
        </div>
        <div class="page-admin__body">
            <keep-alive>
                <router-view></router-view>
            </keep-alive>
        </div>
    </div>
</template>

<script>
    export default {
        name: "AppComponent",
        provide: function () {
            return {
                genres: this.genres
            }
        },
        data: function () {
            return {
                aTopMenuItems: [
                    {
                        href: "/admin",
                        name: "Добавление треков"
                    },
                    {
                        href: "/admin/mediafire",
                        name: "Mediafire"
                    },
                    {
                        href: "/admin/info",
                        name: "Инфо"
                    },
                    {
                        href: "/admin/etc",
                        name: "Разное"
                    }
                ],
                genres: [
                    {
                        name: "commerce",
                        value: 1
                    },
                    {
                        name: "russian",
                        value: 2
                    },
                    {
                        name: "mash-up",
                        value: 3
                    }
                ]
            }
        },
        computed: {},
        methods: {}
    }
</script>