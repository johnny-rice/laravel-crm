<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.activities.index.title')
    </x-slot>

    {!! view_render_event('admin.activities.index.activities.before') !!}

    <!-- Activities Datagrid -->
    <v-activities>
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="activities" />
        
                    <div class="text-xl font-bold dark:text-white">
                        @lang('admin::app.activities.index.title')
                    </div>
                </div>

                <div class="flex gap-2">
                    <i class="icon-list cursor-pointer rounded p-2 text-2xl"></i>
        
                    <i class="icon-calendar cursor-pointe rounded p-2 text-2xl"></i>
                </div>
            </div>

            <!-- DataGrid Shimmer -->
            @if (
                request()->get('view-type') == 'table'
                || ! request()->has('view-type')
            )
                <x-admin::shimmer.datagrid :is-multi-row="true"/>
            @endif
        </div>
    </v-activities>

    {!! view_render_event('admin.activities.index.activities.after') !!}

    @pushOnce('scripts')
        <script 
            type="text/x-template"
            id="v-activities-template"
        >
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div class="flex flex-col gap-2">
                        <x-admin::breadcrumbs name="activities" />
            
                        <div class="text-xl font-bold dark:text-white">
                            @lang('admin::app.activities.index.title')
                        </div>
                    </div>

                    {!! view_render_event('admin.activities.index.toggle_view.before') !!}

                    <div class="flex">
                        <i
                            class="icon-list cursor-pointer rounded-md p-2 text-2xl"
                            :class="{'bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-white': viewType == 'table'}"
                            @click="toggleView('table')"
                        ></i>
            
                        <i
                            class="icon-calendar cursor-pointer rounded-md p-2 text-2xl"
                            :class="{'bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-white': viewType == 'calendar'}"
                            @click="toggleView('calendar')"
                        ></i>
                    </div>

                    {!! view_render_event('admin.activities.index.toggle_view.after') !!}
                </div>

                <!-- DataGrid Shimmer -->
                <div>
                    <template v-if="viewType == 'table'">
                        {!! view_render_event('admin.activities.index.datagrid.before') !!}

                        <x-admin::datagrid
                            src="{{ route('admin.activities.get') }}"
                            :isMultiRow="true"
                            ref="datagrid"
                        >
                            <template #header="{
                                isLoading,
                                available,
                                applied,
                                selectAll,
                                sort,
                                performAction
                            }">
                                <template v-if="isLoading">
                                    <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
                                </template>
                    
                                <template v-else>
                                    <div class="row grid grid-cols-[.3fr_.1fr_.3fr_.5fr] grid-rows-1 items-center gap-x-2.5 border-b px-4 py-2.5 dark:border-gray-800 max-lg:hidden">
                                        <div
                                            class="flex select-none items-center gap-2.5"
                                            v-for="(columnGroup, index) in [['id', 'title', 'created_by_id'], ['is_done'], ['comment', 'lead_title', 'type'], ['schedule_from', 'schedule_to', 'created_at']]"
                                        >
                                            <label
                                                class="flex w-max cursor-pointer select-none items-center gap-1"
                                                for="mass_action_select_all_records"
                                                v-if="! index"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="mass_action_select_all_records"
                                                    id="mass_action_select_all_records"
                                                    class="peer hidden"
                                                    :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                                    @change="selectAll"
                                                >

                                                <span
                                                    class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-600 dark:text-gray-300"
                                                    :class="[
                                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor' : (
                                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-multiple peer-checked:text-brandColor' : ''
                                                        ),
                                                    ]"
                                                >
                                                </span>
                                            </label>
                    
                                            <p class="text-gray-600 dark:text-gray-300">
                                                <span class="[&>*]:after:content-['_/_']">
                                                    <template v-for="column in columnGroup">
                                                        <span
                                                            class="after:content-['/'] last:after:content-['']"
                                                            :class="{
                                                                'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                                                'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                                            }"
                                                            @click="
                                                                available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                                            "
                                                        >
                                                            @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                                        </span>
                                                    </template>
                                                </span>
                    
                                                <i
                                                    class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                                    :class="[applied.sort.order === 'asc' ? 'icon-stats-down': 'icon-stats-up']"
                                                    v-if="columnGroup.includes(applied.sort.column)"
                                                ></i>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Mobile Sort/Filter Header -->
                                    <div class="hidden border-b bg-gray-50 px-4 py-3 text-black dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 max-lg:block">
                                        <div class="flex items-center justify-between">
                                            <!-- Mass Actions for Mobile -->
                                            <div v-if="available.massActions.length">
                                                <label
                                                    class="flex w-max cursor-pointer select-none items-center gap-1"
                                                    for="mass_action_select_all_records"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="mass_action_select_all_records"
                                                        id="mass_action_select_all_records"
                                                        class="peer hidden"
                                                        :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                                        @change="selectAll"
                                                    >

                                                    <span
                                                        class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-600 dark:text-gray-300"
                                                        :class="[
                                                            applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checkbox-select peer-checked:text-brandColor' : (
                                                                applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-multiple peer-checked:text-brandColor' : ''
                                                            ),
                                                        ]"
                                                    >
                                                    </span>
                                                </label>
                                            </div>
                                            
                                            <!-- Mobile Sort Dropdown -->
                                            <div v-if="available.columns.some(column => column.sortable)">
                                                <x-admin::dropdown position="bottom-{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'left' : 'right' }}">
                                                    <x-slot:toggle>
                                                        <div class="flex items-center gap-1">
                                                            <button
                                                                type="button"
                                                                class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-md border bg-white px-2.5 py-1.5 text-center leading-6 text-gray-600 transition-all marker:shadow hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                            >
                                                                <span>
                                                                    Sort
                                                                </span>
                                
                                                                <span class="icon-down-arrow text-2xl"></span>
                                                            </button>
                                                        </div>
                                                    </x-slot>
                            
                                                    <x-slot:menu>
                                                        <x-admin::dropdown.menu.item
                                                            v-for="column in available.columns.filter(column => column.sortable && column.visibility)"
                                                            @click="sort(column)"
                                                        >
                                                            <div class="flex items-center gap-2">
                                                                <span v-html="column.label"></span>
                                                                <i
                                                                    class="align-text-bottom text-base text-gray-600 dark:text-gray-300"
                                                                    :class="[applied.sort.order === 'asc' ? 'icon-stats-down': 'icon-stats-up']"
                                                                    v-if="column.index == applied.sort.column"
                                                                ></i>
                                                            </div>
                                                        </x-admin::dropdown.menu.item>
                                                    </x-slot>
                                                </x-admin::dropdown>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>

                            <template #body="{
                                isLoading,
                                available,
                                applied,
                                selectAll,
                                sort,
                                performAction
                            }">
                                <template v-if="isLoading">
                                    <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
                                </template>
                    
                                <template v-else>
                                    <div
                                        class="row grid grid-cols-[.3fr_.1fr_.3fr_.5fr] grid-rows-1 gap-x-2.5 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 max-lg:hidden"
                                        v-for="record in available.records"
                                    >
                                        <!-- Mass Actions, Title and Created By -->
                                        <div class="flex gap-2.5">
                                            <input
                                                type="checkbox"
                                                :name="`mass_action_select_record_${record.id}`"
                                                :id="`mass_action_select_record_${record.id}`"
                                                :value="record.id"
                                                class="peer hidden"
                                                v-model="applied.massActions.indices"
                                            >
                
                                            <label
                                                class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"
                                                :for="`mass_action_select_record_${record.id}`"
                                            ></label>
                    
                                            <div class="flex flex-col gap-1.5">
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.id }}
                                                </p>

                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.title }}
                                                </p>
                    
                                                <p
                                                    class="text-gray-600 dark:text-gray-300"
                                                    v-html="record.created_by_id"
                                                >
                                                </p>
                                            </div>
                                        </div>
                    
                                        <!-- Is Done -->
                                        <div class="flex gap-1.5">
                                            <div class="flex flex-col gap-1.5">
                                                <p
                                                    class="text-gray-600 dark:text-gray-300"
                                                    v-html="record.is_done"
                                                >
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Comment, Lead Title and Type -->
                                        <div class="flex gap-1.5">
                                            <div class="flex flex-col gap-1.5">
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    {{-- @{{ record.comment }} --}}
                                                    @{{ record.comment.length > 180 ? record.comment.slice(0, 180) + '...' : record.comment }}
                                                </p>

                                                <p v-html="record.lead_title"></p>

                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.type ?? 'N/A'}}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start justify-between gap-x-4">
                                            <div class="flex flex-col gap-1.5">
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.schedule_from ?? 'N/A' }}
                                                </p>
                    
                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.schedule_to }}
                                                </p>

                                                <p class="text-gray-600 dark:text-gray-300">
                                                    @{{ record.created_at }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-1.5">
                                                <p
                                                    class="place-self-end"
                                                    v-if="available.actions.length"
                                                >
                                                    <span
                                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                        :class="action.icon"
                                                        v-text="! action.icon ? action.title : ''"
                                                        v-for="action in record.actions"
                                                        @click="performAction(action)"
                                                    ></span>
                                                </p>
                                            </div>
                                        </div> 
                                    </div>

                                    <!-- Mobile Card View -->
                                    <div
                                        class="hidden border-b px-4 py-4 text-black dark:border-gray-800 dark:text-gray-300 max-lg:block"
                                        v-for="record in available.records"
                                    >
                                        <div class="mb-2 flex items-center justify-between">
                                            <!-- Mass Actions for Mobile Cards -->
                                            <div class="flex w-full items-center justify-between gap-2">
                                                <p v-if="available.massActions.length">
                                                    <label :for="`mass_action_select_record_${record[available.meta.primary_column]}`">
                                                        <input
                                                            type="checkbox"
                                                            :name="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                            :value="record[available.meta.primary_column]"
                                                            :id="`mass_action_select_record_${record[available.meta.primary_column]}`"
                                                            class="peer hidden"
                                                            v-model="applied.massActions.indices"
                                                        >
                
                                                        <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor">
                                                        </span>
                                                    </label>
                                                </p>

                                                <!-- Actions for Mobile -->
                                                <div
                                                    class="flex w-full items-center justify-end"
                                                    v-if="available.actions.length"
                                                >
                                                    <span
                                                        class="dark:hover:bg-gray-80 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200"
                                                        :class="action.icon"
                                                        v-text="! action.icon ? action.title : ''"
                                                        v-for="action in record.actions"
                                                        @click="performAction(action)"
                                                    >
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card Content -->
                                        <div class="grid gap-2">
                                            <template v-for="column in available.columns">
                                                <div class="flex flex-wrap items-baseline gap-x-2">
                                                    <span class="text-slate-600 dark:text-gray-300" v-html="column.label + ':'"></span>
                                                    <span class="break-words font-medium text-slate-900 dark:text-white" v-html="record[column.index]"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </x-admin::datagrid>

                        {!! view_render_event('admin.activities.index.datagrid.after') !!}
                    </template>

                    <template v-else>
                        {!! view_render_event('admin.activities.index.vue_calender.before') !!}

                        <v-calendar></v-calendar>
                        
                        {!! view_render_event('admin.activities.index.vue_calender.after') !!}
                    </template>
                </div>
            </div>
        </script>

        <script
            type="text/x-template"
            id="v-calendar-template"
        >
            <v-vue-cal
                hide-view-selector
                :watchRealTime="true"
                :twelveHour="true"
                :disable-views="['years', 'year', 'month', 'day']"
                style="height: calc(100vh - 240px);"
                :class="{'vuecal--dark': theme === 'dark'}"
                :events="events"
                @ready="getActivities"
                @view-change="getActivities"
                @event-click="goToActivity"
                locale="{{ app()->getLocale() }}"
            ></v-vue-cal>
        </script>

        <script type="module">
            app.component('v-activities', {
                template: '#v-activities-template',

                data() {
                    return {
                        viewType: '{{ request('view-type') }}' || 'table',
                    };
                },

                methods: {
                    /**
                     * Toggle view type.
                     * 
                     * @param {String} type
                     * @return {void}
                     */
                    toggleView(type) {
                        this.viewType = type;

                        let currentUrl = new URL(window.location);

                        currentUrl.searchParams.set('view-type', type);

                        window.history.pushState({}, '', currentUrl);
                    },
                },
            });
        </script>

        <script type="module">
            app.component('v-calendar', {
                template: '#v-calendar-template',

                data() {
                    return {
                        events: [],

                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    };
                },

                mounted() {
                    /**
                     * Listen for the theme change event.
                     * 
                     * @return {void}
                     */
                    this.$emitter.on('change-theme', (theme) => this.theme = theme);
                },

                methods: {
                    /**
                     * Get the activities for the calendar.
                     * 
                     * @param {Object} {startDate}
                     * @param {Object} {endDate}
                     * @return {void} 
                     */
                    getActivities({startDate, endDate}) {
                        this.$root.pageLoaded = false;

                        this.$axios.get("{{ route('admin.activities.get', ['view_type' => 'calendar']) }}" + `&startDate=${new Date(startDate).toLocaleDateString("en-US")}&endDate=${new Date(endDate).toLocaleDateString("en-US")}`)
                            .then(response => {
                                this.events = response.data.activities;
                            })
                            .catch(error => {});
                    },

                    /**
                     * Redirect to the activity edit page.
                     * 
                     * @param {Object} event
                     * @return {void}
                     */
                    goToActivity(event) {
                        if (event.id) {
                            window.location.href = `{{ route('admin.activities.edit', ':id') }}`.replace(':id', event.id);
                        }
                    },
                },
            });
        </script>

        <script>
            /**
             * Update status for `is_done`.
             * 
             * @param {Event} {target}
             * @return {void}
             */
            const updateStatus = ({ target }, url) => {
                axios
                    .post(url, {
                        _method: 'put',
                        is_done: target.checked,
                    })
                    .then(response => {
                        window.emitter.emit('add-flash', { type: 'success', message: response.data.message });
                    })
                    .catch(error => {});
            };
        </script>
    @endPushOnce

    @pushOnce('styles')
        <style>
            .vuecal__event {
                background-color: #0e90d9!important;
                color: #fff!important;
                cursor: pointer
            }

            .vuecal__event.done {
                background-color: #53c41a!important
            }

            .vuecal--dark {
                background-color: #1F2937 !important;
                color: #FFFFFF !important;
                border-color: #374151 !important;
            }

            .vuecal--dark .vuecal__header,
            .vuecal--dark .vuecal__header-weekdays,
            .vuecal--dark .vuecal__header-months {
                background-color: #374151 !important;
                color: #FFFFFF !important;
            }

            .vuecal--dark .vuecal__day,
            .vuecal--dark .vuecal__month-view,
            .vuecal--dark .vuecal__week-view {
                background-color: #1F2937 !important;
                color: #FFFFFF !important;
                border-color: #374151 !important;
            }

            .vuecal--dark .vuecal__day--weekend {
                background-color: #374151 !important;
                color: #FFFFFF !important;
            }

            .vuecal--dark .vuecal__day--selected {
                background-color: #374151 !important;
                color: #FFFFFF !important;
            }

            .vuecal--dark .vuecal__event {
                background-color: #374151 !important;
                color: #FFFFFF !important;
            }

            .vuecal__event {
                height: auto !important;
            }
        </style>
    @endPushOnce
</x-admin::layouts>