<template>
    <tr>
        <td align="center">{{ pos }}.</td>
        <td>{{ item.name_title_case }}</td>
        <td><input type="text" v-model="item.quickkey"/></td>
    </tr>
</template>

<script>
    export default {
        name: "Mp3MediafireComponent",
        props: ['item', 'pos']
    }
</script>