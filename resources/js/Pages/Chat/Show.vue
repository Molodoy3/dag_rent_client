<script setup lang="ts">

import { computed, onMounted, PropType, ref } from "vue";
import MessageData = App.Data.MessageData;
import SendMessageData = App.Data.SendMessageData;
import UserData = App.Data.UserData;
import DefaultLayout from "../Layouts/DefaultLayout.vue";
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import Button from "../Components/Button.vue";

const { chatId, messages } = defineProps({
    messages: Array as PropType<MessageData>,
    chatId: Number,
    companion: Object
})

const form = useForm(<SendMessageData>{
    message: '',
});

const submit = () => {
    form.post(`/chats/${chatId}/message`, {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
    });
};

const messagesList = ref<MessageData[]>(messages);
const messagesRef = ref(null);

const page = usePage();
const user = computed<UserData>(() => page.props.auth.user);

onMounted(() => {
    window.Echo.private(`Chat.${chatId}`).listen(
        'MessageSent',
        (e: MessageData) => {
            const message: MessageData = e.message;
            message.isOwn = message.user.id === user.value.id;
            messagesList.value.push(message);
            scrollToBottom();
        }
    )
    scrollToBottom();
})

const scrollToBottom = () => {
    if (messagesRef.value) {
        setTimeout(
            () =>
            (messagesRef.value.scrollTop =
                messagesRef.value.scrollHeight),
            10
        );
    }
};

</script>

<template>
    <DefaultLayout>
        <Head title="Аккаунты"></Head>
        <section class="chat">
            <div class="chat__container">
                <div class="chat__user order__user">
                    <div class="order__user-icon icon-user">
                        <img :src="companion.icon" alt="icon user">
                    </div>
                    <div class="user-product__info">
                        <Link :href="route('user.show', companion.id)" class="user-product__name">{{
                            companion.name }}</Link>
                        <div class="user-product__count-sales">{{ companion.countSales }} продаж</div>
                    </div>
                </div>
                <div class="chat__messages" ref="messagesRef">
                    <div v-for="message in messagesList" class="chat__message"
                        :class="{ 'chat__message_own': message.isOwn,
                                  'chat__message_notification': message.user.id == null }">
                        <div class="chat__notification-title" v-if="message.user.id == null"><p>Оповещение</p></div>
                        <p>{{ message.message }}</p>
                        <span class="chat__time" :class="{ 'chat__time_own': message.isOwn }">{{ message.createdAt
                            }}</span>
                    </div>
                </div>
                <div class="chat__bottom">
                    <input @keydown.enter.prevent="submit" type="text" class="chat__input input" v-model="form.message"
                        placeholder="Введи свое сообщение..">
                    <button @click="submit" type="submit" class="chat__submit button">
                        <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" width="20" fill="#000000">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path fill="#000000"
                                    d="M14,3 C14.5523,3 15,3.44772 15,4 L15,12 C15,12.5523 14.5523,13 14,13 C13.4477,13 13,12.5523 13,12 L13,4 C13,3.44772 13.4477,3 14,3 Z M8.70711,4.29289 L12.4142,8 L8.70711,11.7071 C8.31658,12.0976 7.68342,12.0976 7.2929,11.7071 C6.90237,11.3166 6.90237,10.6834 7.2929,10.2929 L8.58579,9 L2,9 C1.44771,9 1,8.55228 1,8 C1,7.44772 1.44771,7 2,7 L8.58579,7 L7.29289,5.70711 C6.90237,5.31658 6.90237,4.68342 7.29289,4.29289 C7.68342,3.90237 8.31658,3.90237 8.70711,4.29289 Z">
                                </path>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </section>
    </DefaultLayout>
</template>

<style scoped></style>
