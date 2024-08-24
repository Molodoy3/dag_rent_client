<script setup lang="ts">

import { Head, Link, useForm } from "@inertiajs/vue3";
import DefaultLayout from "../Layouts/DefaultLayout.vue";
import { route } from "../../../../vendor/tightenco/ziggy";
import { onMounted } from "vue";
import { initCustomSelect } from '../../modules/customSelect.js';
import InputError from "../Components/InputError.vue";


const props = defineProps({
    'product': Object,
    'errors': Object
});

const form = useForm({
    tariff_id: 0
});

function onTariffChange(id) {
    form.tariff_id = id;
}

onMounted(() => {
    initCustomSelect(false);
})

function getHours(time) {
    return time.split(':')[0] + ' ч.'; // Делим строку по ':' и берём первый элемент
}

function formatNumber(number) {
    return Number(number).toString(); // Преобразуем в число и обратно в строку
}

function submitForm() {
    form.post(route('products.buy', { 'product_id': props.product.id }),
        {
            //сохранение состояния (без перезагрузки)
            preserveState: true,
            preserveScroll: true,
        }
    );
}

</script>

<template>
    <DefaultLayout>

        <Head title="Оформление заказа"></Head>
        <section class="order">
            <div class="order__container">
                <div class="order__body">
                    <h1 class="order__title title">Оформление заказа</h1>
                    <div class="order__user">
                        <div class="order__user-icon icon-user">
                            <img :src="product.user.icon" alt="icon user">
                        </div>
                        <div class="user-product__info">
                            <Link :href="route('user.show', product.user.id)" class="user-product__name">{{ product.user.name }}</Link>
                            <div class="user-product__count-sales">{{ product.user.countSales }} продаж</div>
                        </div>
                    </div>
                    <h2 class="order__product-title">{{ product.title }}</h2>
                    <div class="order__description" >{{ product.description }}</div>
                    <Link v-if="$page.props.auth.id == product.user.id" :href="route('accounts.edit', product.id)"
                        class="order__button-edit button button_blue">
                    Редактировать
                    </Link>
                    <div class="order__tariffs form__custom-select-body" data-custom-select="Выберите тариф">
                        <select v-model="form.tariff_id" class="form__select">
                            <option v-for="tariff in product.tariffs" :value='tariff.id'>{{ getHours(tariff.time) }} —
                                {{ formatNumber(tariff.price) }}р.
                            </option>
                        </select>
                        <div class="form__custom-select custom-select-form input">
                            <div data-custom-select-field class="custom-select-form__field "
                                :class="{ 'error': props.errors.tariff_id }"></div>
                            <ul data-custom-select-options class="custom-select-form__options">
                                <li @click="onTariffChange(tariff.id)" v-for="tariff in product.tariffs"
                                    :data-custom-select-option="tariff.id">{{ getHours(tariff.time) }} —
                                    {{ formatNumber(tariff.price) }}р.
                                </li>
                            </ul>
                        </div>
                    </div>
                    <input-error>{{ form.errors.tariff_id }}</input-error>
                    <button @click="submitForm()" class="order__button-buy button">Купить</button>
                    <input-error class="order__error" v-if="form.errors.user">{{ form.errors.user }}</input-error>
                    <input-error class="order__error" v-if="form.errors.balance">{{ form.errors.balance }}</input-error>
                </div>
            </div>
        </section>
    </DefaultLayout>
</template>

<style scoped></style>
