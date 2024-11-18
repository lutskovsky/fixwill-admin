<script setup>
import 'bootstrap/dist/css/bootstrap.min.css';
import "ag-grid-enterprise";
import {ref} from 'vue';
import {AgGridVue} from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS required by the Data Grid
import "ag-grid-community/styles/ag-theme-balham.css"; // Optional Theme applied to the Data Grid
import Multiselect from 'vue-multiselect'


const AG_GRID_LOCALE_RU = {
    // Выбор фильтра
    selectAll: '(Выделить все)',
    selectAllSearchResults: '(Выделить все результаты поиска)',
    searchOoo: 'Поиск...',
    blanks: '(Пусто)',
    noMatches: 'Нет совпадений',

    // Числовой фильтр & текстовый фильтр
    filterOoo: 'Фильтрация...',
    equals: 'Равно',
    notEqual: 'Не равно',
    empty: 'Выберите один',

    // Числовой фильтр
    lessThan: 'Меньше, чем',
    greaterThan: 'Больше, чем',
    lessThanOrEqual: 'Меньше или равно',
    greaterThanOrEqual: 'Больше или равно',
    inRange: 'В промежутке',
    inRangeStart: 'от',
    inRangeEnd: 'до',

    // Текстовый фильтр
    contains: 'Содержит',
    notContains: 'Не содержит',
    startsWith: 'Начинается с',
    endsWith: 'Кончается на',

    // Фильтр даты
    dateFormatOoo: 'dd-mm-yyyy',

    // Условия фильтрации
    andCondition: 'И',
    orCondition: 'ИЛИ',

    // Кнопки фильтра
    applyFilter: 'Применить',
    resetFilter: 'Сбросить',
    clearFilter: 'Очистить',
    cancelFilter: 'Отменить',

    // Заголовки фильтра
    textFilter: 'Текстовый фильтр',
    numberFilter: 'Числовой фильтр',
    dateFilter: 'Фильтр по дате',
    setFilter: 'Выбрать фильтр',

    // Боковая панель
    columns: 'Столбцы',
    filters: 'Фильтры',

    // Панель инструментов столбцов
    pivotMode: 'Режим сводной таблицы',
    groups: 'Группы строк',
    rowGroupColumnsEmptyMessage: 'Перетащите сюда для группировки по строкам',
    values: 'Значения',
    valueColumnsEmptyMessage: 'Перетащите сюда для агрегации',
    pivots: 'Заголовки столбцов',
    pivotColumnsEmptyMessage: 'Перетащите сюда для задания заголовков столбцам',

    // Заголовок группы столбцов по умолчанию
    group: 'Группа',

    // Другое
    loadingOoo: 'Загрузка...',
    noRowsToShow: 'Нет данных',
    enabled: 'Включено',

    // Меню
    pinColumn: 'Закрепить столбец',
    pinLeft: 'Закрепить слева',
    pinRight: 'Закрепить справа',
    noPin: 'Не закреплять',
    valueAggregation: 'Агрегация по значению',
    autosizeThiscolumn: 'Автоматически задавать размер этого столбца',
    autosizeAllColumns: 'Автоматически задавать размер всем столбцам',
    groupBy: 'Группировать по',
    ungroupBy: 'Разгруппировать по',
    resetColumns: 'Сбросить столбцы',
    expandAll: 'Развернуть все',
    collapseAll: 'Свернуть все',
    copy: 'Копировать',
    ctrlC: 'Ctrl+C',
    copyWithHeaders: 'Копировать с заголовками',
    paste: 'Вставить',
    ctrlV: 'Ctrl+V',
    export: 'Экспорт',
    csvExport: 'Экспорт в CSV (.csv)',
    excelExport: 'Экспорт в Excel (.xlsx)',
    excelXmlExport: 'Экспорт в XML (.xml)',

    // Агрегирование корпоративного меню и строки состояния
    sum: 'Сумма',
    min: 'Минимум',
    max: 'Максимум',
    none: 'Пусто',
    count: 'Количество',
    avg: 'Среднее значение',
    filteredRows: 'Отфильтровано',
    selectedRows: 'Выбрано',
    totalRows: 'Всего строк',
    totalAndFilteredRows: 'Строки',
    more: 'многих',
    to: 'из',
    of: 'по',
    page: 'Страница',
    nextPage: 'Следующая страница',
    lastPage: 'Последняя страница',
    firstPage: 'Первая страница',
    previousPage: 'Предыдущая страница',

    // Корпоративное меню (графики)
    pivotChartAndPivotMode: 'Сводная диаграмма & режим сведения',
    pivotChart: 'Сводная диаграмма',
    chartRange: 'Диапазон диаграммы',

    columnChart: 'Столбиковая диаграмма',
    groupedColumn: 'Сгруппированная',
    stackedColumn: 'Сложенная',
    normalizedColumn: '100% Сложенная',

    barChart: 'Панель',
    groupedBar: 'Сгруппированная',
    stackedBar: 'Сложенная',
    normalizedBar: '100% Сложенная',

    pieChart: 'Круговая диаграмма',
    pie: 'Круговая диаграмма',
    doughnut: 'Кольцевая диаграмма',

    line: 'Линия',

    xyChart: 'X Y (Разброс)',
    scatter: 'Диаграмма рассеяния',
    bubble: 'Пузырьковая диаграмма',

    areaChart: 'Область',
    area: 'Диаграмма с областями',
    stackedArea: 'Сложенная',
    normalizedArea: '100% Сложенная',

    histogramChart: 'Гистограмма',

    // Графики
    pivotChartTitle: 'Сводная диаграмма',
    rangeChartTitle: 'График диапазона',
    settings: 'Настройки',
    data: 'Данные',
    format: 'Формат',
    categories: 'Категории',
    defaultCategory: '(Пусто)',
    series: 'Серии',
    xyValues: 'X Y Значения',
    paired: 'Парный режим',
    axis: 'Ось',
    navigator: 'Навигация',
    color: 'Цвет',
    thickness: 'Толщина',
    xType: 'X Тип',
    automatic: 'Автоматически',
    category: 'Категория',
    number: 'Число',
    time: 'Время',
    xRotation: 'X Поворот',
    yRotation: 'Y Поворот',
    ticks: 'Отметки',
    width: 'Ширина',
    height: 'Высота',
    length: 'Длина',
    padding: 'Внутренний отступ',
    spacing: 'Отступ',
    chart: 'Диаграмма',
    title: 'Заголовок',
    titlePlaceholder: 'Заголовок диаграммы - двойной щелчок для редактирования',
    background: 'Фон',
    font: 'Шрифт',
    top: 'Верх',
    right: 'Право',
    bottom: 'Низ',
    left: 'Лево',
    labels: 'Метки',
    size: 'Размер',
    minSize: 'Минимальный размер',
    maxSize: 'Максимальный размер',
    legend: 'Легенда',
    position: 'Позиция',
    markerSize: 'Размер маркера',
    markerStroke: 'Обводка маркера',
    markerPadding: 'Внутренний отступ маркера',
    itemSpacing: 'Расстояние между предметами',
    itemPaddingX: 'Внутренний отступ предмета по X',
    itemPaddingY: 'Внутренний отступ предмета по Y',
    layoutHorizontalSpacing: 'Горизонтальный отступ',
    layoutVerticalSpacing: 'Вертикальный отступ',
    strokeWidth: 'Ширина обводки',
    offset: 'Смещение',
    offsets: 'Смещения',
    tooltips: 'Всплывающие подсказки',
    callout: 'Вызов',
    markers: 'Маркеры',
    shadow: 'Тень',
    blur: 'Размытие',
    xOffset: 'Смещение по X',
    yOffset: 'Смещение по Y',
    lineWidth: 'Ширина линии',
    normal: 'Нормальный',
    bold: 'Жирный',
    italic: 'Наклоненный',
    boldItalic: 'Жирный наклоненный',
    predefined: 'Предопределенный',
    fillOpacity: 'Непрозрачность заливки',
    strokeOpacity: 'Непрозрачность линии',
    histogramBinCount: 'Количество сегментов',
    columnGroup: 'Столбец',
    barGroup: 'Панель',
    pieGroup: 'Круговая',
    lineGroup: 'Линейная',
    scatterGroup: 'X Y (Разброс)',
    areaGroup: 'Зональная',
    histogramGroup: 'Гистограмма',
    groupedColumnTooltip: 'Сгруппированная',
    stackedColumnTooltip: 'Сложенная',
    normalizedColumnTooltip: '100% Сложенная',
    groupedBarTooltip: 'Сгруппированная',
    stackedBarTooltip: 'Сложенная',
    normalizedBarTooltip: '100% Сложенная',
    pieTooltip: 'Круговая',
    doughnutTooltip: 'Кольцевая',
    lineTooltip: 'Линейная',
    groupedAreaTooltip: 'Зональная',
    stackedAreaTooltip: 'Сложенная',
    normalizedAreaTooltip: '100% Сложенная',
    scatterTooltip: 'Рассеяния',
    bubbleTooltip: 'Пузырьковая',
    histogramTooltip: 'Гистограмма',
    noDataToChart: 'Нет данных для представления в виде диаграммы.',
    pivotChartRequiresPivotMode: 'Для сводной диаграммы необходимо включить режим сводной диаграммы.',
    chartSettingsToolbarTooltip: 'Меню',
    chartLinkToolbarTooltip: 'Связать с сеткой',
    chartUnlinkToolbarTooltip: 'Не связывать с сеткой',
    chartDownloadToolbarTooltip: 'Загрузить диаграмму',

    // ARIA
    ariaHidden: 'скрытый',
    ariaVisible: 'видимый',
    ariaChecked: 'проверенный',
    ariaUnchecked: 'непроверенный',
    ariaIndeterminate: 'неопределенный',
    ariaColumnSelectAll: 'Переключить на выделение всех столбцов',
    ariaInputEditor: 'Редактор ввода',
    ariaDateFilterInput: 'Ввод фильтра даты',
    ariaFilterInput: 'Ввод фильтра',
    ariaFilterColumnsInput: 'Ввод фильтра столбцов',
    ariaFilterValue: 'Значение фильтра',
    ariaFilterFromValue: 'Фильтровать от значения',
    ariaFilterToValue: 'Фильтровать до значения',
    ariaFilteringOperator: 'Оператор фильтрации',
    ariaColumnToggleVisibility: 'переключить видимость столбца',
    ariaColumnGroupToggleVisibility: 'переключить видимость группы столбцов',
    ariaRowSelect: 'Нажмите ПРОБЕЛ для выделения данной строки',
    ariaRowDeselect: 'Нажмите ПРОБЕЛ для снятия выделения данной строки',
    ariaRowToggleSelection: 'Нажмите ПРОБЕЛ, чтобы переключить выделение строки',
    ariaRowSelectAll: 'Нажмите ПРОБЕЛ, чтобы переключить выделение всех строк',
    ariaSearch: 'Поиск',
    ariaSearchFilterValues: 'Поиск значений по фильтру',
}

// Register Ag-Grid component
function roundedAvgFunc(params) {
    const values = params.values || [];
    if (values.length === 0) return 0;
    const sum = values.reduce((acc, val) => acc + val, 0);
    const average = sum / values.length;
    return Math.round(average * 100) / 100; // Round to 2 decimal places
}

function roundedSumFunc(params) {
    const values = params.values || [];
    const sum = values.reduce((acc, val) => acc + val, 0);
    return Math.round(sum * 100) / 100; // Round to 2 decimal places
}

const props = defineProps({
    orderStatuses: Object,
    orderTypes: Object,
    presets: Object,
});

const gridApi = ref(null);
const presets = ref(props.presets);
const selectedPresetName = ref('');
const loadButtonText = ref('Загрузить заказы');

const selectedTypes = ref([]);
const selectedStatuses = ref([]);
const startDate = ref("");
const endDate = ref("");
const rowData = ref([]);

const presetName = ref('');
const columnDefs = ref([
    {
        headerName: "Дата закрытия",
        field: "date",
        filter: 'agDateColumnFilter',
        filterParams: {
            inRangeInclusive: true,
            comparator: function (filterLocalDateAtMidnight, cellValue) {
                if (cellValue == null) return -1;
                const cellDate = new Date(cellValue);
                return cellDate - filterLocalDateAtMidnight;
            },
            browserDatePicker: true
        },
    },
    {headerName: "Заказ", field: "label"},
    {headerName: "Стоимость заказа", field: "revenue"},
    {headerName: "Себестоимость материалов", field: "costParts"},
    {headerName: "Сумма себестоимости материалов", field: "costParts", aggFunc: roundedSumFunc},
    {headerName: "Стоимость услуг", field: "costOps"},
    {headerName: "Валовая прибыль", field: "netProfit"},
    {headerName: "Средняя валовая прибыль", field: "netProfit", aggFunc: roundedAvgFunc},
    {headerName: "Сумма валовой прибыли", field: "netProfit", aggFunc: roundedSumFunc},
    {headerName: "Город", field: "city"},
    {headerName: "Статус", field: "status"},
    {headerName: "Тип устройства", field: "device_type"},
    {headerName: "Бренд", field: "brand"},
    {headerName: "Диагональ", field: "diagonal"},
    {headerName: "Согласовальщик", field: "soglas"},
]);

// Grid options
const gridOptions = ref({
    localeText: AG_GRID_LOCALE_RU,
    sideBar: 'columns',
    defaultColDef: {
        sortable: true,
        filter: true,
        enableRowGroup: true,
        resizable: true,
        enableValue: true,
        filterParams: {
            buttons: ['reset', 'apply'],
        }
    },
    rowGroupPanelShow: 'always',
    animateRows: true,
});

const onGridReady = (params) => {
    gridApi.value = params.api;
    // columnApi.value = params.columnApi;
};


// Fetch data on button click
const loadData = () => {
    const statusIds = selectedStatuses.value.map((status) => status.id);
    const typeIds = selectedTypes.value.map((type) => type.id);

    if (!startDate || !endDate) {
        alert("Please select both start and end dates.");
        return;
    }

    try {
        const fetchUrl = route('report.orders')

        loadButtonText.value = "Загружаем...";

        axios.get(fetchUrl, {
            params: {
                startDate: startDate.value,
                endDate: endDate.value,
                types: typeIds,
                statuses: statusIds,
            }
        })
            .then(function (response) {
                rowData.value = response.data;
                loadButtonText.value = "Загрузить заказы";
            })

        // const data = await response.json();
        // rowData.value = data;
    } catch (error) {
        console.error("Error loading data:", error);
    }
};

const savePreset = () => {
    const statusIds = selectedStatuses.value;
    const typeIds = selectedTypes.value;

    const colState = gridApi.value.getColumnState();
    const filterState = gridApi.value.getFilterModel();
    // localStorage.setObject('colState', gridOptions.columnApi.getColumnState());
    // // localStorage.setObject('groupState', gridOptions.gridColumnApi.getColumnGroupState());
    // // localStorage.setObject('sortState', gridOptions.api.getSortModel());
    // localStorage.setObject('filterState', gridOptions.api.getFilterModel());

    const newPreset = {
        name: presetName.value,
        settings: {
            statuses: statusIds,
            types: typeIds,
            colState: colState,
            filterState: filterState,
        },
    }


    presets.value.push(newPreset);

    // settings = JSON.stringify(settings);

    try {
        const savePresetUrl = route('report.preset.store')

        axios.post(savePresetUrl, newPreset)
        // const data = await response.json();
        // rowData.value = data;
    } catch (error) {
        console.error("Error loading data:", error);
    }
};

const presetChange = (selectedOption, id) => {
    console.log(selectedOption);
    const presetStatuses = selectedOption.settings.statuses;
    const presetTypes = selectedOption.settings.types;
    const colState = selectedOption.settings.colState;
    const filterState = selectedOption.settings.filterState;
    selectedPresetName.value = selectedOption.name;
    console.log(selectedOption.name);
    selectedStatuses.value = presetStatuses;
    selectedTypes.value = presetTypes;

    gridApi.value.applyColumnState({
        state: colState,
        applyOrder: true,
    });
    gridApi.value.setFilterModel(filterState);
};
</script>

<template>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="form-group form-inline">
                    <label class="form-label" for="start-date">Начало периода:</label>
                    <input id="start-date" v-model="startDate" class="form-control" type="date"/>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="end-date">Конец периода:</label>
                <input id="end-date" v-model="endDate" class="form-control" type="date"/>
            </div>

            <div class="col-md-3">

                <label class="form-label" for="types">Типы заказа:</label>
                <multiselect id="types" v-model="selectedTypes" :clear-on-select="false" :close-on-select="false"
                             :multiple="true" :options="$page.props.orderTypes"
                             :preselect-first="false" :preserve-search="true" label="name" placeholder="Типы заказа"
                             track-by="id">
                    <template #selection="{ values, search, isOpen }">
                        <span v-if="values.length"
                              v-show="!isOpen"
                              class="multiselect__single">Выбрано {{ values.length }}</span>
                    </template>
                </multiselect>
            </div>

            <div class="col-md-3">

                <label class="form-label" for="statuses">Статусы заказа:</label>
                <multiselect id="statuse" v-model="selectedStatuses" :clear-on-select="false"
                             :close-on-select="false" :multiple="true" :options="$page.props.orderStatuses"
                             :preselect-first="false" :preserve-search="true" label="name" placeholder="Статусы заказа"
                             track-by="id">
                    <template #selection="{ values, search, isOpen }">
                        <span v-if="values.length"
                              v-show="!isOpen"
                              class="multiselect__single">Выбрано {{ values.length }}</span>
                    </template>
                </multiselect>
            </div>


        </div>
        <div class="row mb-4">
            <div class="col-md-3">
                <multiselect :clear-on-select="false" :close-on-select="true" :options="$page.props.presets"
                             :preselect-first="false"
                             :preserve-search="true" label="name" placeholder="Пресеты" track-by="id"
                             @select="presetChange">
                    <template slot="singleLabel"><strong>{{ selectedPresetName }}</strong></template>
                </multiselect>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <input v-model="presetName" placeholder="Название нового пресета" style="width: 100%" type="text">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" @click="savePreset">Сохранить пресет</button>
            </div>

        </div>
        <div class="row mb-4">

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" @click="loadData">{{ loadButtonText }}</button>
            </div>


        </div>

        <div style="width: 100%; height: 800px;">
            <ag-grid-vue
                :columnDefs="columnDefs"
                :gridOptions="gridOptions"
                :rowData="rowData"
                class="ag-theme-balham"

                style="width: 100%; height: 100%"
                @grid-ready="onGridReady"
            />
        </div>
    </div>
</template>


<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>
.ag-theme-alpine {
    width: 100%;
    height: 100%;
}

</style>
