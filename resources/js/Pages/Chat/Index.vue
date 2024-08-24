<script setup lang="ts">

import DefaultLayout from "../Layouts/DefaultLayout.vue";
import {Head, Link} from "@inertiajs/vue3";
import {route} from "../../../../vendor/tightenco/ziggy";

const { chats } = defineProps({
    'chats': Object
})

</script>

<template>
    <DefaultLayout>
        <Head title="Аккаунты"></Head>
        <section class="chats">
            <div class="chats__container">
                <div class="chats__items">
                    <Link :href="route('chats.show', chat.id)" v-for="chat in chats" class="chats__item">
                        <div class="chats__user order__user">
                            <div class="icon-user">
                                <img :src="chat.user.icon" alt="user icon">
                            </div>
                            <div class="user-product__info">
                                <div class="user-product__name">{{ chat.user.name }}</div>
                                <div class="chats__last-message">
                                    {{ chat.user.lastMessage.message }}
                                    {{ chat.user.lastMessage.createdAt }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </DefaultLayout>
</template>

<style scoped>

</style>
