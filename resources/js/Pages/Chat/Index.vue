<script setup lang="ts">

import DefaultLayout from "../Layouts/DefaultLayout.vue";
import {Head, Link, router, useForm, usePage} from "@inertiajs/vue3";
import {route} from "../../../../vendor/tightenco/ziggy";
import {computed, nextTick, onUnmounted, ref} from "vue";
import UserData = App.Data.UserData;
import MessageData = App.Data.MessageData;
import SendMessageData = App.Data.SendMessageData;
import Button from "../Components/Button.vue";
import InputError from "../Components/InputError.vue";

const page = usePage();
const user = computed<UserData>(() => page.props.auth.user);

const props = defineProps({
    'chats': Object,
    'errors': Object,
    'chatID': Number,
    'companion': Object,
    'messagesList': Object
})

const chats = ref(props.chats);

//токен для post fetch запроса
const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
const csrf = metaElements.length > 0 ? metaElements[0].content : "";

//слушаем все чаты пользователя
window.Echo.private(`UserChats.${user.value.id}`).listen('MessageSent', async (e) => {
    updateChats();
})

async function updateChats() {
    try {
        chats.value = await fetch(`/get-chats`, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: csrf,
                //userID: user.value.id
            })
        }).then(response => response.json());
    } catch (e) {
        console.error(e);
    }
}

let currentChatId = null; // Переменная для хранения текущего ID чата
let currentChannel = null; // Переменная для хранения текущего WebSocket канала
async function chooseChat(id) {
    //route('chats.show', chat.id);

    try {
        const params = new URLSearchParams({
            //обязательно если есть пагинация указываем номер страницы и конечно из props обязательно
            //page: props.accounts.current_page
        });

        //console.log(await fetch(`/chats/${id}`)
        //.then(response => response.text()))
        const chat = await fetch(`/chats/get`, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: csrf,
                chatID: id,
                //userID: user.value.id
            })
        })
            .then(response => response.json());

        // Отключаем слушателя для текущего канала
        if (currentChannel) {
            currentChannel.stopListening('MessageSent'); // Остановить слушателя
        }

        chatId.value = chat.chatId;
        companion.value = chat.companion;
        messagesList.value = chat.messages;


        //отправляем для чтения сообщений
        await fetch(`/chat/read`, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: csrf,
                chatID: chatId.value,
                userID: user.value.id
            })
        })
        updateChats();

        //когда загружаются dom элементы, скролим
        await nextTick();

        scrollToBottom();

        // Подключаем WebSocket к новому чату
        currentChatId = id; // Обновляем текущий ID чата
        currentChannel = window.Echo.private(`Chat.${chatId.value}`); // Создаем новое подключение

        currentChannel.listen('MessageSent', async (e) => {
            const message = e.message;
            message.isOwn = message.user.id === user.value.id;
            lastMessage.value = message;
            messagesList.value.push(message);
            //если получаем чужое сообщение, делаем то, что оно прочитано
            if (!message.isOwn) {
                //отправляем для чтения сообщений
                await fetch(`/chat/read`, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        _token: csrf,
                        chatID: chatId.value,
                        userID: user.value.id
                    })
                })
            }

            scrollToBottom();
        });

        //console.log(chat)
    } catch (error) {
        console.error('Ошибка при получении данных:', error);
    }
}

onUnmounted(() => {
    // Отключаем слушателя для текущего канала
    if (currentChannel) {
        currentChannel.stopListening('MessageSent'); // Остановить слушателя
    }
})

const companion = ref(props.companion);
const chatId = ref(props.chatID);
const messagesList = ref<MessageData[]>(props.messagesList);
const messagesRef = ref(null);

const lastMessage = ref('');

const form = useForm(<SendMessageData>{
    message: '',
});

const submit = () => {
    form.post(`/chats/${chatId.value}/message`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        },
    });
};

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
        <Head title="Чаты"></Head>
        <section class="chats">
            <div class="chats__container">
                <div class="chats__items active">
                    <div @click="chooseChat(chat.id)" v-for="chat in chats" class="chats__item"
                         :class="{
                        'chats__item_not-read': !chat.lastMessage.isOwn && !chat.lastMessage.isRead,
                        'chats__item_focus': chat.id == chatId
                    }">
                        <div class="chats__user order__user">
                            <div class="icon-user">
                                <img :src="chat.user.icon" alt="user icon">
                            </div>
                            <div class="user-product__info">
                                <div class="user-product__name">{{ chat.user.name }}</div>
                                <div class="chats__last-message">
                                    <span v-if="chat.id == chatId && lastMessage.message">
                                        <span class="chats__last-message-text">{{ lastMessage.message }}</span>
                                        <span class="chats__date-last-message">{{ lastMessage.createdAt }}</span>
                                    </span>
                                    <span v-else>
                                        <span class="chats__last-message-text">{{ chat.lastMessage.message }}</span>
                                      <span class="chats__date-last-message">{{
                                              chat.lastMessage.createdAt
                                          }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="messagesList" class="chats__body">
                    <button class="chats__back button">назад</button>
                    <div class="chat">
                        <div class="chat__user order__user">
                            <div class="order__user-icon icon-user">
                                <img :src="companion.icon" alt="icon user">
                            </div>
                            <div class="user-product__info">
                                <Link :href="route('user.show', companion.id)" class="user-product__name">{{
                                        companion.name
                                    }}
                                </Link>
                                <div class="user-product__count-sales">{{ companion.countSales }} продаж</div>
                            </div>
                        </div>
                        <div class="chat__messages" ref="messagesRef">
                            <div v-for="message in messagesList" class="chat__message" :class="{
                                'chat__message_own': message.isOwn,
                                'chat__message_notification': message.user.id == null
                            }">
                                <div class="chat__notification-title" v-if="message.user.id == null">
                                    <p>
                                        Оповещение</p>
                                </div>
                                <p>{{ message.message }}</p>
                                <span class="chat__time" :class="{ 'chat__time_own': message.isOwn }">{{
                                        message.createdAt
                                    }}</span>
                            </div>
                        </div>
                        <input-error v-if="props.errors.message" style="margin-bottom: -10px; margin-left: 5px">
                            {{ props.errors.message }}
                        </input-error>
                        <div class="chat__bottom">
                            <input @keydown.enter.prevent="submit" type="text" class="chat__input input"
                                   v-model="form.message" placeholder="Введи свое сообщение..">
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
                </div>
            </div>
        </section>
    </DefaultLayout>
</template>

<style scoped></style>
