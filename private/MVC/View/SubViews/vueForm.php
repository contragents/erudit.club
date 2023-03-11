<main class="page__main main">
    <div class="wrapper">
        <div class="main__container" id="app">
            <section class="main__product main-row product">
                <p class="product__navigation">
                    <a class="product__link" href="#">{{ Lot.category }}</a>
                    /
                    <a class="product__link" href="#">{{ Lot.sub_category }}</a>
                </p>
                <h2 class="product__title">{{ Lot.brand }}</h2>
                <h3 class="product__subtitle">{{ Lot.name }}</h3>
                <div class="product__cost">{{ Lot.price }} ₽</div>
            </section>
            <!-- Slider main container -->
            <div class="swiper">
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->
                    <div v-for="item of Lot.pictures" v-if="item != ''" class="swiper-slide">
                        <img :src="item" alt="">
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
            <section class="main__description main-row description">
                <h2 class="description__title main-row__title">Описание</h2>
                <div class="btn-arrow">
                    <img class="btn-arrow-img" src="img/arrow.svg" alt="">
                </div>
                <div class="description__box box-toggle">
                    <p class="description__text">
                        {{ Lot.descr }}<br/>
                        <span>Состав:</span><br/>
                        {{ Lot.composition }}<br/>
                        <span>Цвет</span>:<br/>
                        {{ Lot.color }}
                    </p>
                </div>
            </section>
            <section class="main__sizes main-row sizes">
                <h2 class="sizes__title main-row__title">Размер и крой</h2>
                <div class="btn-arrow">
                    <img class="btn-arrow-img" src="img/arrow.svg" alt="">
                </div>
                <div class="sizes__box box-toggle">
                    <table class="sizes__table">
                        <tr>
                            <td class="sizes__table-col1">Глубина</td>
                            <td class="sizes__table-col2">{{ Lot.depth }} см</td>
                        </tr>
                        <tr>
                            <td class="sizes__table-col1">Высота</td>
                            <td class="sizes__table-col2">{{ Lot.height }} см</td>
                        </tr>
                        <tr>
                            <td class="sizes__table-col1">Ремень</td>
                            <td class="sizes__table-col2">{{ Lot.strap }} см</td>
                        </tr>
                        <tr>
                            <td class="sizes__table-col1">Ширина</td>
                            <td class="sizes__table-col2">{{ Lot.width }} см</td>
                        </tr>
                    </table>
                </div>
            </section>
            <section class="main__date main-row date">
                <h2 class="date__title main-row__title">
                    Расчетные даты отправления
                </h2>
                <p class="date__text">{{ Lot.shipDate }}</p>
            </section>
            <section class="main__contacts main-row contacts">
                <h2 class="contacts__title main-row__title">
                    Контактная информация
                </h2>
                <div class="btn-arrow">
                    <img class="btn-arrow-img" src="img/arrow.svg" alt="">
                </div>
                <div class="contacts__box box-toggle">
                    <form class="contacts__form" id="contacts_form">

                        <input
                                type="checkbox"
                                class="agree__custom-checkbox"
                                id="agree"
                                name="agree"
                                v-model="offerCheckBox"
                                :disabled="offerAgreed"
                        />
                        <label for="agree">Я соглашаюсь с
                            <a class="agree__link" href="#">условиями Оферты</a></label
                        <span></span>
                        <p v-if="offerTextError" class="agree__error">
                            Для продолжения заказа необходимо подтвердить согласие с офертой
                        </p>

                        <input v-model="email" :disabled="!verifyCodeFormHidden || emailPresent"
                               type="email" class="contacts__inp" id="email"
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                               placeholder="mail@example.com"/>

                        <button v-if="!emailPresent" :disabled="verifyButtonDisabled || emailPresent"
                                onClick="VerifyEmail();return false;"
                                class="order__button contacts__btn"
                                v-bind:class="{ active: !verifyButtonDisabled && !emailPresent }"
                                id="button">Выслать код
                        </button>

                        <p v-if="!verifyCodeFormHidden" class="contacts__inp-confirm-text">Мы отправили на Ваш адрес
                            электронной почты 6-ти значный код подтвержения.
                            Пожалуйста, подтвердите Ваш адрес почты</p>

                        <input v-if="!verifyCodeFormHidden" :disabled="codeVerified" type="number" min="100000"
                               class="contacts__inp"
                               id="check" placeholder="Код из Email"/>

                        <button v-if="!verifyCodeFormHidden" :disabled="checkButtonDisabled"
                                onClick="checkEmailCode();return false;"
                                class="order__button contacts__btn"
                                v-bind:class="{ active: !checkButtonDisabled }">Отправить код
                        </button>

                        <input v-if="codeVerified || emailPresent" v-model="phone" :disabled="phonePresent" type="tel"
                               class="contacts__inp"
                               id="tel" minlength="7" maxlength="16"
                               pattern="[\+0-9\-]{7,16}$"
                               placeholder="Телефон +79123456789"
                               title="Формат номера +79123456789"
                               required>

                        <input v-if="codeVerified || emailPresent" v-model="name" :disabled="namePresent" type="text"
                               class="contacts__inp"
                               id="fio" placeholder="Фамилия Имя Отчество" required>

                        <input v-if="codeVerified || emailPresent"
                               v-model="index"
                               type="number"
                               minlength="6"
                               maxlength="6"
                               min="100000"
                               max="999999"
                               class="contacts__inp" id="index"
                               placeholder="Почтовый индекс"
                               title="Почтовый индекс 6 цифр"
                               required>

                        <!--<input v-if="codeVerified || emailPresent" v-model="address" type="text" class="contacts__inp"
                               id="address1"
                               v-bind:class="{ contacts__inp__mail_check: addressVerified }"
                               placeholder="Москва Ватрушинская ул д 56 кв 45" required>
                        <label v-if="codeVerified || emailPresent" for="address1"></label>-->


                        <!--<textarea v-if="codeVerified || emailPresent"
                                  v-model="address"
                                  id="address"
                                  v-bind:class="{ contacts__inp__mail_check: addressVerified }"
                                  class="contacts__inp_adress"
                                  name="adress"
                                  placeholder="Адрес доставки"
                                  style="overflow: hidden;"
                                  rows="3">
                        </textarea>
                        <label v-if="codeVerified || emailPresent" for="address"></label>-->

                        <label v-if="codeVerified || emailPresent"
                               v-bind:class="{ contacts__inp_adress_arrow: addressVerified }"
                               for="address">
                        </label>
                        <textarea v-if="codeVerified || emailPresent"
                                  v-model="address"
                                  id="address"
                                  class="contacts__inp-adress"
                                  v-bind:class="{ contacts__inp_adress_check: addressVerified }"
                                  name="address"
                                  placeholder="Адрес доставки"
                                  style="overflow: hidden"
                                  rows="3">
                        </textarea>


                        <button v-if="codeVerified || emailPresent" :disabled="false"
                                onClick="return checkAddress();"
                                style="margin-bottom:0; margin-top:0.2em;"
                                class="order__button contacts__btn"
                                v-bind:class="{ active: true }">Рассчитать доставку
                        </button>
                    </form>
                    <!--<p class="contacts__error">Ошибка в контактах!</p>-->
                </div>
            </section>
            <section v-if="addressVerified" class="main__delivery main-row delivery">
                <h2 class="delivery__title main-row__title">Способ доставки</h2>
                <div class="btn-arrow">
                    <img class="btn-arrow-img" src="img/arrow.svg" alt=""/>
                </div>
                <div class="delivery__box box-toggle">
                    <form class="delivery__form">
                        <div class="delivery__form-row">
                            <input
                                    v-model="deliverySelector"
                                    type="radio"
                                    class="delivery__custom-radio"
                                    id="deliv"
                                    name="deliv"
                                    :value="pvz_value"
                            />
                            <label for="deliv">
                                <span class="delivery__text">Пункт выдачи заказов CDEK<br>
                    <b class="delivery__adress">{{  pvz_address  }} тел:&nbsp;{{  pvz1_tel  }}</b></span>
                                <span class="delivery__cost">{{  pvz_price  }} Р</span>
                            </label>
                            <p class="delivery__hint">от 2 до 6 дней</p>
                        </div>
                        <div v-if="pvz1_code" class="delivery__form-row">
                            <input
                                    v-model="deliverySelector"
                                    type="radio"
                                    class="delivery__custom-radio"
                                    id="deliv1"
                                    name="deliv"
                                    :value="pvz1_value"
                            />
                            <label for="deliv1">
                                <span class="delivery__text">Пункт выдачи заказов CDEK<br>
                    <b class="delivery__adress">{{  pvz1_address  }} тел:&nbsp;{{  pvz1_tel  }}</b></span>
                                <span class="delivery__cost">{{  pvz1_price  }} Р</span>
                            </label>
                            <p class="delivery__hint">от 2 до 6 дней</p>
                        </div>
                        <div class="delivery__form-row">
                            <input
                                    v-model="deliverySelector"
                                    type="radio"
                                    class="delivery__custom-radio"
                                    id="deliv2"
                                    name="deliv"
                                    :value="courier_value"
                            />
                            <label for="deliv2">
                                <span class="delivery__text">
                      Курьер CDEK <br>
                      <b class="delivery__adress">{{  courier_address  }}</b>
                    </span>
                                <span class="delivery__cost">{{  courier_price  }} Р</span>
                            </label>
                            <p class="delivery__hint">от 3 до 7 дней</p>
                        </div>
                        <p class="delivery__error"></p>
                    </form>
                </div>
            </section>
            <section class="main__contacts main-row contacts" style="padding-bottom:0;">

                <div class="main__payment main-row payment">
                    <select v-if="codeVerified || emailPresent"
                            id="p-select"
                            class="payment__select"
                            v-model="payment"
                            style="margin-bottom:0.4em;">
                        <option v-for="option in options" v-bind:value="option.value" class="payment__option">
                            {{ option.text }}
                        </option>
                    </select>
                    <label for="p-select"></label>
                </div>
            </section>

            <div class="main__payment main-row">
                <form class="contacts__form">
                    <button v-if="codeVerified || emailPresent" class="order__button"
                            v-bind:class="{ active: true }"
                            id="button"
                            onClick="sendOrder();return false;">
                        Отправить заказ
                    </button>
                </form>
            </div>

        </div>

    </div>
</main>