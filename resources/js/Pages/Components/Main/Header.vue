<script setup>
import {Link, router} from "@inertiajs/vue3";


</script>

<template>
    <header class="header">
        <div class='header__container'>
            <Link :href="route('products.index')" class="header__logo logo">
                <img src='../../../../img/logo.png' alt='logo'>
            </Link>
            <div class='header__menu menu'>
                <div class='menu__icon'>
                    <span></span>
                </div>
                <nav class='menu__body'>
                    <ul v-if="$page.props.auth && $page.props.auth.isAdmin" class='menu__list'>
                        <li><Link :href="route('accounts.index')" class='menu__link'>Аккаунты</Link></li>
                        <li><Link :href="route('games.index')" class='menu__link'>Игры</Link></li>
                        <li><Link :href="route('platforms.index')" class='menu__link'>Платформы</Link></li>
                        <li><Link :href="route('statistics.index')" class='menu__link'>Статистика</Link></li>
                        <li><Link :href="route('chats.index')" class='menu__link'>Чаты</Link></li>
                    </ul>
                </nav>
            </div>
            <div v-if="$page.props.auth && $page.props.auth.id" class="header__profile">
                <div class="header__balance">
                    {{ $page.props.auth.user.balance }}р.
                </div>
                <Link :href="route('user.show', $page.props.auth.id)" class="header__icon">
                    <img :src="$page.props.auth.icon" alt="icon">
                </Link>
            </div>
            <Link v-else :href="route('login')" class="header__profile button">Войти</Link>
        </div>
    </header>
</template>

<style scoped>

</style>
