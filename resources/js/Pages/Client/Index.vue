<script setup lang="ts">

import {Head, Link, router} from "@inertiajs/vue3";
import DefaultLayout from "../Layouts/DefaultLayout.vue";
import Title from "../Components/Title.vue";
import {onMounted, onUnmounted, ref, watch} from "vue";
import {initCustomSelect} from "../../modules/customSelect";
import Button from "../Components/Button.vue";

const props = defineProps({
    'accounts': Object,
    "platforms": Object,
    "games": Object
});

onUnmounted(() => {

});
onMounted(() => {
    initCustomSelect(false);

    //const select = document.querySelector('[data-options]');
    //const searchItems = select.querySelectorAll('option');
    const input = document.querySelector('[data-input-search]');
    const customSelect = document.querySelector('[data-custom-select-options]');
    const searchCustomItems = customSelect.querySelectorAll('li');

    input.addEventListener('input', function (e) {
        const target = e.target;

        const val = target.value.trim().toUpperCase();
        const fragment = document.createDocumentFragment();

        //if (!target.classList.contains('keyboard__key')) return;

        for (const elem of searchCustomItems) {
            elem.remove();

            if (val === '' || elem.textContent.toUpperCase().includes(val)) {
                fragment.append(elem);
            }
        }

        customSelect.append(fragment);
    })
});

const accounts = ref(props.accounts);
watch(() => props.accounts, (newAccounts) => {
    accounts.value = newAccounts;
});

const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
const csrf = metaElements.length > 0 ? metaElements[0].content : "";

async function updateProducts() {
    try {
        const params = new URLSearchParams({
            search: search.value,
            platform_id: platform_id.value,
            game_id: game_id.value,
            //обязательно если есть пагинация указываем номер страницы и конечно из props обязательно
            page: props.accounts.current_page
        });
        accounts.value = await fetch(`/get-update-products?${params}`, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                _token: csrf,
                //userID: user.value.id
            })
        })
            .then(response => response.json());
    } catch (error) {
        console.error('Ошибка при получении данных:', error);
    }
}

//следим за поисковой строкой
let search = ref('');
watch(search, () => {
    checkCurrentPage()
});

//следим за изменением платформы в фильтре
let platform_id = ref('');
watch(platform_id, () => {
    checkCurrentPage()
});

//следим за изменением игры в фильтре
let game_id = ref('');
watch(game_id, () => {
    checkCurrentPage()
});

//сброс текущей стр до 1, если при поиске она не 1
function checkCurrentPage() {
    if (accounts.value.current_page > 1) {
        router.get(
            "/products",
            {search: search.value, platform_id: platform_id.value, game_id: game_id.value, page: 1},
            {
                //сохранение состояния (без перезагрузки)
                preserveState: true,
                preserveScroll: true,
            }
        );
    } else
        updateProducts();
}


function onPlatformChange(id) {
    platform_id.value = id;
}

function onGameChange(id) {
    game_id.value = id;
}

function nextPaginate(url) {
    router.get(
        url,
        {search: search.value, platform_id: platform_id.value, game_id: game_id.value},
        {
            //сохранение состояния (без перезагрузки)
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function formatNumber(number) {
    return Number(number).toString(); // Преобразуем в число и обратно в строку
}

</script>

<template>
    <DefaultLayout>
        <Head title="Предложения"></Head>
        <section class="products accounts">
            <div class="products__container">
                <div class="accounts__header">
                    <Title>Все предложения</Title>
                    <div class="search-col">
                        <div class="form__custom-select-body" data-custom-select="Игра">
                            <select data-options v-model="game_id" id="platformId" class="form__select">
                                <option value="0">Игра</option>
                                <option :selected="game.id == game_id" v-for="game in games" :value='game.id'>{{
                                        game.name
                                    }}
                                </option>
                            </select>
                            <div class="form__custom-select custom-select-form input">
                                <input style="visibility:collapse; margin: 0; position: absolute;" type="text"
                                       class="custom-select-form__field custom-select-form__input input"
                                       data-input-search
                                       placeholder="Игра">
                                <div data-custom-select-field class="custom-select-form__field"></div>
                                <ul data-custom-select-options class="custom-select-form__options">
                                    <li data-custom-select-option="0" @click="onGameChange('0')">Игра</li>
                                    <li @click="onGameChange(game.id)" v-for="game in games"
                                        :data-custom-select-option="game.id">{{ game.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="form__custom-select-body" data-custom-select="Платформа">
                            <select data-options v-model="platform_id" id="platformId" class="form__select">
                                <option value="0">Платформа</option>
                                <option :selected="platform.id == platform_id" v-for="platform in platforms"
                                        :value='platform.id'>{{
                                        platform.name
                                    }}
                                </option>
                            </select>
                            <div class="form__custom-select custom-select-form input">
                                <input style="visibility:collapse; margin: 0; position: absolute;" type="text"
                                       class="custom-select-form__field custom-select-form__input input"
                                       data-input-search
                                       placeholder="Платформа">
                                <div data-custom-select-field class="custom-select-form__field"></div>
                                <ul data-custom-select-options class="custom-select-form__options">
                                    <li data-custom-select-option="0" @click="onPlatformChange('0')">Платформа</li>
                                    <li @click="onPlatformChange(platform.id)" v-for="platform in platforms"
                                        :data-custom-select-option="platform.id">{{ platform.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <input class="statistics__search input" v-model="search" type="text"
                               placeholder="Поиск аккаунтов..."/>
                    </div>
                </div>
                <div class="products__items">
                    <Link :href="route('products.show', account.id)" v-for="account in accounts.data"
                          class="products__item">
                        <div class="products__item-col products__item-col_first">
                            <div class="products__info">
                                <h3 class="products__games statistics__account-games"><span
                                    v-for="game in account.games">{{
                                        game.name
                                    }}</span>
                                </h3>
                                <div class="products__platform accounts__platform">{{ account.platform.name }}</div>
                            </div>
                            <div class="products__title">{{ account.title }}</div>
                        </div>
                        <div class="products__item-col">
                            <div class="products__user user-product">
                                <div class="user-product__icon icon-user">
                                    <img :src="account.user.icon" alt="icon user">
                                </div>
                                <div class="user-product__info">
                                    <div class="user-product__name">{{ account.user.name }}</div>
                                    <div class="user-product__count-sales">{{ account.user.countSales }} продаж</div>
                                </div>
                            </div>
                            <div class="products__price">от {{ formatNumber(account.minTariff) }}р.</div>
                        </div>
                    </Link>
                </div>
                <ul class="pagination">
                    <li v-if="accounts.links.length > 3" v-for="link in accounts.links">
                        <button v-if="link.url && !link.active" :class="{ 'active': link.active, 'link': link.url }"
                                @click="nextPaginate(link.url)" v-html="link.label">
                        </button>
                        <span v-else v-html="link.label" :class="{ 'active': link.active }"></span>
                    </li>
                </ul>
            </div>
        </section>
    </DefaultLayout>
</template>

<style scoped></style>
