<template>
    <tr>
        <td align="center" :colspan="colspanAmount">пусто</td>
    </tr>
</template>

<script>
    export default {
        name: "TrEmptyComponent",
        props: ['colspanAmount']
    }
</script>