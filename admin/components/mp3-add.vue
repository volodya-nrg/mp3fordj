<template>
    <tr>
        <td align="center">{{ item.pos }}.</td>
        <td align="center"><input type="checkbox" v-model="item.checked" @change="onChangeCheckbox"/></td>
        <td align="center">
            <img v-if="item.cover" width="26" height="26" :src="item.cover"/>
            <img v-else width="26" height="26" src="/img/cover_default.jpg"/>
        </td>
        <td>
            <input v-if="item.edit" type="text" v-model="item.author"/>
            <span v-else>{{ item.author }}</span>
        </td>
        <td>
            <input v-if="item.edit" type="text" v-model="item.title"/>
            <span v-else>{{ item.title }}</span>
        </td>
        <td>
            <input v-if="item.edit" type="text" v-model="item.album"/>
            <span v-else>{{ item.album }}</span>
        </td>
        <td>
            <input v-if="item.edit" type="text" v-model="item.dop_style"/>
            <span v-else>{{ item.dop_style }}</span>
        </td>
        <td>
            <input v-if="item.edit" type="text" v-model="item.label"/>
            <span v-else>{{ item.label }}</span>
        </td>
        <td align="center">{{ item.duration }}</td>
        <td>
            <img v-if="item.wave" border="0" width="156" height="26" :src="item.wave" />
            <div v-else style="width:156px; height:26px; overflow:hidden">
                <object id="pl"
                        classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
                        codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=11,0,0,0"
                        width="600"
                        height="100"
                        align="middle">
                    <param name="allowScriptAccess" value="sameDomain" />
                    <param name="allowFullScreen" value="true" />
                    <param name="movie" value="/flash/flash_waveform.swf" />
                    <param name="loop" value="false" />
                    <param name="quality" value="high" />
                    <param name="bgcolor" value="#EEEEEE" />
                    <param name="wmode" value="opaque">
                    <param name="flashvars" :value="'file=' + item.flash_file + '&urlOutput=' + item.flash_url_output">
                    <embed src="/flash/flash_waveform.swf"
                           :flashvars="'file=' + item.flash_file + '&urlOutput=' + item.flash_url_output"
                           loop="false"
                           quality="high"
                           wmode="opaque"
                           bgcolor="#EEEEEE"
                           width="600"
                           height="100"
                           name="pl"
                           align="middle"
                           allowScriptAccess="sameDomain"
                           allowFullScreen="true"
                           type="application/x-shockwave-flash"
                           pluginspage="http://www.macromedia.com/go/getflashplayer" />
                </object>
            </div>
        </td>
        <td align="center">
            <a v-show="!item.edit" href="javascript:void(0);" @click="item.edit = !item.edit">edit</a>
            <a v-show="item.edit" href="javascript:void(0);" @click="save">save</a>
        </td>
    </tr>
</template>

<script>
    export default {
        name: "Mp3AddComponent",
        props: ['item'],
        inject: ['onChangeCheckbox'],
        methods: {
            save: function () {
                this.item.edit = !this.item.edit;

                this.updateItem(this.item);
            },
            updateItem: function (item) {
                for (var prop in item) {
                    var value = item[prop];

                    if (typeof(value) === 'string' && value && prop !== 'filename') {
                        item[prop] = value.trim();
                    }
                }
            }
        }
    }
</script>