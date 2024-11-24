<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4"> {{ title }}</h1>

        <div v-if="$page.props.flash.message" class="bg-green-100 text-green-700 p-2 mb-4 rounded">
            {{ $page.props.flash.message }}
        </div>

        <form class="space-y-4 size-80" @submit.prevent="submit">
            <!-- Hidden Field for clientId -->
            <input v-model="form.clientId" type="hidden"/>
            <input v-model="form.orderLabel" type="hidden"/>

            <div>
                <label class="text-gray-700">Имя:</label>
                <input
                    v-model="form.name"
                    class="mt-1 w-full border rounded p-2 h-6"
                    type="text"

                />
            </div>

            <div>
                <label class="block text-gray-700">Адрес:</label>
                <input
                    v-model="form.address"
                    class="mt-1 block w-full border rounded p-2 h-6"
                    type="text"

                />
            </div>

            <div>
                <label class="block text-gray-700">Email:</label>
                <input
                    v-model="form.email"
                    class="mt-1 block w-full border rounded p-2 h-6"
                    type="email"

                />
            </div>
            <div>
                <label class="block text-gray-700">Примечание:</label>
                <textarea
                    v-model="form.notes"
                    class="mt-1 block w-full border rounded p-2 h-6"
                    rows="4"
                />
            </div>

            <div>
                <label class="block text-gray-700">Юрлицо:</label>

                <input
                    v-model="form.legalEntity"
                    class="mt-1 block h-5 w-5 h-6"
                    type="checkbox"
                />

            </div>


            <div v-for="field in form.customFields" v-if="clientId">
                <template v-if="!field.legal || (field.legal && form.legalEntity) ">

                    <label class="block text-gray-700">{{ field.name }}:</label>

                    <span
                        v-if="field.type !== 0"
                    > {{ field.value }} </span>

                    <span
                        v-else
                    >{{ field.value ? 'Да' : 'Нет' }} </span>
                </template>

            </div>


            <h1 class="text-xl font-bold ">Телефоны</h1>
            <div v-for="phone in form.phones">
                <input
                    :id="phone.encrypted" v-model="phone.text"
                    class="mt-1 block w-full border rounded p-2 h-6"
                    type="text"
                />
                <p><a :data-encrypted="phone.encrypted" :data-phonetext="phone.text" href="#" @click.prevent="call">Позвонить</a>
                    <a
                    class="text-red-800" href="#" @click.prevent="removePhone(phone)">Удалить</a></p>

            </div>

            <!--            <h2 class="font-bold"></h2>-->
            <div>
                <!--                <input-->
                <!--                    v-model="newPhone"-->
                <!--                    class="mt-1 block w-full border rounded p-2 h-6 "-->
                <!--                    type="text"-->
                <!--                />-->
                <p><a href="#" @click.prevent="addPhone">Добавить телефон</a></p>

            </div>

            <h1 v-if="clientId" class="font-bold">Выбрать АОН</h1>
            <label v-for="number in form.virtualNumbers">
                <input
                    v-model="selectedVirtualNumber"
                    :value="number.number"
                    name="numberGroup"
                    type="radio"
                />
                {{ number.number }} - {{ number.description }} <br>

            </label>

            <div>
                <button
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    type="submit"
                >
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import {useForm} from '@inertiajs/vue3';
// import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

let id = 0;

export default {
    props: {
        orderLabel: String,
        clientData: Object,
        clientId: Number,
        phones: Object,
        clientCustomFields: Object,
        customFieldsSettings: Object,
        virtualNumbers: Object,
    },
    data() {
        return {
            selectedVirtualNumber: '',
            newPhone: '',
        }
    },
    computed: {
        title() {
            return this.clientId ? `Клиент в заказе ${this.orderLabel}` : "Создание нового заказа и клиента";
        },
    },
    setup(props) {

        // const { flash } = usePage().props.value;

        // const page = usePage();

        let formCustomFields = [];
        for (let field of props.customFieldsSettings) {
            const key = 'f' + field.id;

            field.value = props.clientCustomFields?.[key] || '';
            formCustomFields.push(field);
        }

        const form = useForm({
            clientId: props.clientId || null,
            orderLabel: props.orderLabel || null,
            name: props.clientData?.name || '',
            address: props.clientData?.address || '',
            email: props.clientData?.email || '',
            notes: props.clientData?.notes || '',
            legalEntity: props.clientData?.legalEntity || false,
            customFields: formCustomFields,
            phones: props.phones || [],
            virtualNumbers: props.virtualNumbers || [],
        });


        const submit = () => {
            form.post(route('order.create', props.clientId), {
                onError: () => {
                    // Handle errors if needed
                },
            });
        };

        return {
            orderLabel: props.orderLabel,
            form,
            submit
        };
    },

    methods: {
        call(e) {
            const phoneText = e.target.dataset.phonetext;
            const encryptedPhone = e.target.dataset.encrypted;
            const virtualNumber = this.selectedVirtualNumber;
            if (!virtualNumber) {
                alert("Не выбран АОН");
                return;
            }
            // debugger;
            // console.log(this.callRoute);
            axios.post(route('employee.call'), {
                encryptedPhone: encryptedPhone,
                phoneText: phoneText,
                virtualNumber: virtualNumber
            })
                .then(response => {
                    console.log('Response:', response.data);
                    // Handle successful response (e.g., show a notification, update state)
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Handle error (e.g., show an error message)
                });
        },
        addPhone() {
            this.form.phones.push({text: "", encrypted: id++});
            // this.newPhone = '';
        },
        removePhone(phone) {
            this.form.phones = this.form.phones.filter((t) => t !== phone);
        },
    },
};
</script>

<style scoped>
/* Add component-specific styles if necessary */
</style>
