<template>
    <JuniorHighlayout>
        <div class="pt-[24px] pl-[10px] pr-[10px] lg:pl-[32px] lg:pr-[86px] pb-36">
            <div class="space-y-[32px] gap-y-[32px] text-[#030303]">
                <div>
                    <nav
                        aria-label="Breadcrumb"
                        class="text-sm font-medium mb-8"
                    >
                        <ol class="flex items-center space-x-[5px]">
                            <li>
                                <a
                                    :href="route('junior-high.manager')"
                                    class="transition-colors duration-200"
                                >
                                    {{ $t("content.common.manager") }}
                                </a>
                            </li>
                            <li>
                                <span>></span>
                            </li>
                            <li>
                                <span aria-current="page">
                                    {{ $t("common.title.classrooms") }}
                                </span>
                            </li>
                        </ol>
                    </nav>
                    <div class="flex items-center mb-8 gap-2">
                      <a href="javascript:history.back()"
                            class="text-gray-600 hover:text-gray-800 border border-gray-500 rounded bg-white p-1"
                        >
                            <ChevronLeftIcon class="h-4 w-4" />
                    </a>
                        <h1
                            class="text-[29px] font-normal text-[#091E42] leading-6"
                        >
                            {{ $t("common.title.classrooms") }}
                        </h1>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="w-full">
                        <div
                            class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0 md:space-x-4">
                            <div class="relative w-full md:w-[17rem]  text-xs flex items-center cursor-pointer">
                                <VueMultiselect
                                    ref="multiselect"
                                    v-model="gradeSelected"
                                    :options="$page.props.assignedGrades"
                                    track-by="id"
                                    label="name"
                                    :searchable="true"
                                    :allow-empty="true"
                                    :close-on-select="true"
                                    :show-labels="true"
                                    :max-height="330"
                                    placeholder="All Grades"
                                    name="school-select"
                                    class="multiselect-custom border border-[#D1D5DB] rounded-md placeholder:text-[#030303]"
                                    @select="gradeSelected"
                                    @remove="gradeSelected=null"

                                >
                                    <!-- <template #caret>
                                        <div
                                            class="absolute right-2 top-1/2 -translate-y-1/2"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="21"
                                                height="22"
                                                viewBox="0 0 21 22"
                                                fill="none"
                                                class="transition-transform duration-200"
                                                :class="{
                                                    'rotate-180': isOpen,
                                                }"
                                            >
                                                <path
                                                    d="M6.2998 8.8999L10.4998 13.0999L14.6998 8.8999"
                                                    stroke="#3D3D3D"
                                                    stroke-width="1.575"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>
                                    </template> -->
                                </VueMultiselect>
                            </div>
                            <RosterMenu
                                @openAddNewTeacher="openAddNewTeacher"
                                @openAddNewStudent="openAddNewStudent"
                                @openAddNewClassroom="openAddNewClassrrom"
                            />

                            <!-- <div class="relative inline-block">
                                <VueMultiselect v-model="classSelected" :options="classroomOptions" track-by="value"
                                    label="name" :searchable="true" :max-height="330"
                                    placeholder="All Classes" name="school-select" class="dropdown-selector w-[164px]">
                                </VueMultiselect>
                            </div> -->

                        </div>
                    </div>

                    <div
                        class="flex items-center space-x-4 text-[#030303] text-sm font-semibold mt-4 mb-8"
                    >
                        <!-- <p>Schools: <span class="font-normal">6</span></p> commented as schooladmin has one school only -->
                        <p>
                            {{ $t("common.table_heading.classes") }}:
                            <span class="font-normal">{{
                                counts.classrooms
                            }}</span>
                        </p>
                        <p v-if="counts.teacherCount > 1">
                            {{ $t("common.table_heading.tw_remaining") }}:
                            <span class="font-normal">{{
                                counts.teacherCount
                            }}</span>
                        </p>
                        <p>
                            {{ $t("common.table_heading.sw_remaining") }}:
                            <span class="font-normal">{{ counts.studentCount }}</span>
                        </p>
                    </div>

                    <div
                        class="border bg-[#F5F6F7] border-[#DFE2E6] rounded-md"
                    >
                        <div class="pt-4 px-[21px]">
                            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                                <div class="relative flex-grow">
                                    <svg
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                    <div class="relative">
                                        <div class="relative">
                                            <input
                                                type="text"
                                                placeholder="Search"
                                                class="w-full h-[36px] pl-10 pr-10 py-2 bg-white rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200"
                                                v-model="searchClassroom"
                                                @keyup.enter="findClassroom"
                                            />
                                            <svg
                                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                />
                                            </svg>
                                        </div>
                                        <svg
                                            v-if="searchClassroom"
                                            @click="resetButton"
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-700 h-5 w-5 cursor-pointer"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex gap-3 items-center">
                                    <button
                                        @click="findClassroom"
                                        class="formButtons text-sm text-white"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            width="16"
                                            height="16"
                                            fill="white"
                                        >
                                            <path
                                                d="M10 14L4 5V3H20V5L14 14V20L10 22V14Z"
                                            ></path>
                                        </svg>
                                        {{ $t("common.table_heading.filter") }}
                                    </button>
                                    <!-- <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600"
                                            >Edit</span
                                        >
                                        <button @click="toggleEdit" type="button" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none" :class="[!classroomsEdit ? 'training' : 'bg-gray-200']" role="switch">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out" :class="[!classroomsEdit ? 'translate-x-6' : 'translate-x-1']">
                                            </span>
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                            <div v-if="isLoading">
                                <LoadingSpinner :isLoading="isLoading"/>
                            </div>

                            <div v-else-if="Object.keys(classrooms.data).length"
                            class="overflow-x-auto overflow-y-visible lg:overflow-visible text-xs xl:text-sm">
                            <table class="min-w-full divide-y divide-gray-300 mb-48 xl:mb-10 ">

                                    <thead>
                                        <tr
                                            class="text-left text-[#111827] text-[13px]"
                                        >
                                            <th class="p-4 w-8">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-gray-300"
                                                    v-model="selectAll"
                                                    @change="toggleSelectAll"
                                                />
                                            </th>
                                            <th class="p-4 w-1/4">
                                                {{
                                                    $t(
                                                        "common.table_heading.classroom"
                                                    )
                                                }}
                                            </th>
                                            <th class="p-4 w-1/4">
                                                {{
                                                    $t(
                                                        "common.table_heading.teacher"
                                                    )
                                                }}
                                            </th>
                                            <!-- <th class="p-4">School</th> -->
                                            <th class="p-4 w-1/4">
                                                {{
                                                    $t(
                                                        "common.table_heading.grade"
                                                    )
                                                }}
                                            </th>
                                            <th class="p-4 w-1/4">
                                                {{
                                                    $t(
                                                        "common.table_heading.students_count"
                                                    )
                                                }}
                                            </th>
                                            <th class="p-4 w-1/4">
                                                {{
                                                    $t(
                                                        "common.table_heading.actions"
                                                    )
                                                }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="classroom in classrooms.data"
                                            :key="classroom.id"
                                            class="even:bg-[#E7E7E7] text-xs xl:text-sm text-[#3D3D3D]"
                                        >
                                            <td class="p-4">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-gray-300"
                                                    v-model="classroom.selected"
                                                />
                                            </td>
                                            <td
                                                class="p-4 max-h-20 overflow-y-auto w-1/4"
                                            >
                                                {{ classroom?.name }}
                                            </td>
                                            <td
                                                class="p-4 max-h-20 overflow-y-auto w-1/4"
                                            >
                                                {{ classroom?.teacher?.name }}
                                            </td>
                                            <!-- <td class="p-4 text-gray-900">
                                                {{ classroom?.school?.name }}
                                            </td> -->
                                            <td
                                                class="p-4 text-gray-900 max-h-20 overflow-y-auto w-1/4"
                                            >
                                                {{ getGradeNumber(gradeWiseDataShow(
                                                    classroom.teacher.valid_grades,
                                                    classroom.teacher.extra_classroom_grade,
                                                    classroom.teacher.teacher_classrooms,
                                                    classroom
                                                )) }}
                                            </td>
                                            <td
                                                class="p-4 max-h-20 overflow-y-auto w-1/4"
                                            >
                                                {{
                                                    classroom?.users
                                                        ? classroom?.users
                                                              ?.length
                                                        : 0
                                                }}
                                            </td>
                                            <td class="p-4">
                                                <div class="relative">
                                                    <Menu
                                                        as="div"
                                                        class="relative inline-block text-left"
                                                    >
                                                        <div>
                                                            <MenuButton
                                                                class="flex items-center rounded-full bg-white p-1 border border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100"
                                                            >
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 20 20"
                                                                    fill="none"
                                                                >
                                                                    <path
                                                                        d="M10 3C10.3978 3 10.7794 3.15804 11.0607 3.43934C11.342 3.72064 11.5 4.10218 11.5 4.5C11.5 4.89782 11.342 5.27936 11.0607 5.56066C10.7794 5.84196 10.3978 6 10 6C9.60218 6 9.22064 5.84196 8.93934 5.56066C8.65804 5.27936 8.5 4.89782 8.5 4.5C8.5 4.10218 8.65804 3.72064 8.93934 3.43934C9.22064 3.15804 9.60218 3 10 3ZM10 8.5C10.3978 8.5 10.7794 8.65804 11.0607 8.93934C11.342 9.22064 11.5 9.60218 11.5 10C11.5 10.3978 11.342 10.7794 11.0607 11.0607C10.7794 11.342 10.3978 11.5 10 11.5C9.60218 11.5 9.22064 11.342 8.93934 11.0607C8.65804 10.7794 8.5 10.3978 8.5 10C8.5 9.60218 8.65804 9.22064 8.93934 8.93934C9.22064 8.65804 9.60218 8.5 10 8.5ZM11.5 15.5C11.5 15.1022 11.342 14.7206 11.0607 14.4393C10.7794 14.158 10.3978 14 10 14C9.60218 14 9.22064 14.158 8.93934 14.4393C8.65804 14.7206 8.5 15.1022 8.5 15.5C8.5 15.8978 8.65804 16.2794 8.93934 16.5607C9.22064 16.842 9.60218 17 10 17C10.3978 17 10.7794 16.842 11.0607 16.5607C11.342 16.2794 11.5 15.8978 11.5 15.5Z"
                                                                        fill="#030303"
                                                                    />
                                                                </svg>
                                                            </MenuButton>
                                                        </div>

                                                        <transition
                                                            enter-active-class="transition ease-out duration-100"
                                                            enter-from-class="transform opacity-0 scale-95"
                                                            enter-to-class="transform opacity-100 scale-100"
                                                            leave-active-class="transition ease-in duration-75"
                                                            leave-from-class="transform opacity-100 scale-100"
                                                            leave-to-class="transform opacity-0 scale-95"
                                                        >
                                                            <MenuItems
                                                                class="absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                                                            >
                                                                <div
                                                                    class="py-1"
                                                                >
                                                                    <MenuItem
                                                                        v-slot="{
                                                                            active,
                                                                        }">
                                                                    <a @click="fetchClassroomInfo(classroom.id, classroom.grade_name)" :class="[
                                                                                active
                                                                                    ? 'bg-gray-100 text-gray-900 outline-none'
                                                                                    : 'text-gray-700',
                                                                                'block px-4 py-2 text-sm',
                                                                            ]"
                                                                            >Class
                                                                            Info</a
                                                                        >
                                                                    </MenuItem>
                                                                    <!-- <MenuItem
                                                                        v-slot="{
                                                                            active,
                                                                        }"
                                                                    >
                                                                        <a
                                                                            href="#"
                                                                            :class="[
                                                                                active
                                                                                    ? 'bg-gray-100 text-gray-900 outline-none'
                                                                                    : 'text-gray-700',
                                                                                'block px-4 py-2 text-sm',
                                                                            ]"
                                                                            >Analytics</a
                                                                        >
                                                                    </MenuItem> -->
                                                                    <!-- <MenuItem
                                                                        v-slot="{
                                                                            active,
                                                                        }"
                                                                    >
                                                                        <a
                                                                            href="#"
                                                                            :class="[
                                                                                active
                                                                                    ? 'bg-gray-100 text-gray-900 outline-none'
                                                                                    : 'text-gray-700',
                                                                                'block px-4 py-2 text-sm',
                                                                            ]"
                                                                            >Export
                                                                            class</a
                                                                        >
                                                                    </MenuItem> -->
                                                                    <!-- <MenuItem
                                                                        v-slot="{
                                                                            active,
                                                                        }"
                                                                    >
                                                                        <a
                                                                            href="#"
                                                                            :class="[
                                                                                active
                                                                                    ? 'bg-gray-100 text-gray-900 outline-none'
                                                                                    : 'text-gray-700',
                                                                                'block px-4 py-2 text-sm',
                                                                            ]"
                                                                            >Archieve
                                                                            class</a
                                                                        >
                                                                    </MenuItem> -->
                                                                    <hr
                                                                        class="my-1 mx-auto w-[85%] border-[#DFE2E6]"
                                                                    />
                                                                    <MenuItem
                                                                        v-slot="{
                                                                            active,
                                                                        }"
                                                                    >
                                                                        <button
                                                                            type="submit"
                                                                            :class="[
                                                                                active
                                                                                    ? 'error-msg bg-gray-100 outline-none'
                                                                                    : 'error-msg',
                                                                                'block w-full px-4 py-2 text-left text-sm',
                                                                            ]"
                                                                            @click="openDeleteConfirm(classroom)"
                                                                        >
                                                                            <div
                                                                                class="flex items-center gap-2"
                                                                            >
                                                                                <svg
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    width="16"
                                                                                    height="16"
                                                                                    viewBox="0 0 16 16"
                                                                                    fill="none"
                                                                                >
                                                                                    <path
                                                                                        d="M4 12L12 4M4 4L12 12"
                                                                                        stroke="currentColor"
                                                                                        stroke-width="2"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                    />
                                                                                </svg>
                                                                                Remove
                                                                            </div>
                                                                        </button>
                                                                    </MenuItem>
                                                                </div>
                                                            </MenuItems>
                                                        </transition>
                                                    </Menu>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="overflow-x-auto text-lg font-bold p-5 text-center">
                                {{$t("V2.roster.add_new_class.header.no_data_found")}}
                            </div>
                        </div>
                    </div>

                </div>
                <Pagination
                        v-if="classrooms.meta.total >classrooms.meta.per_page"
                        class="mt-6"
                        :links="classrooms.meta.links"
                        :requestData="{}"
                    ></Pagination>
            </div>

        </div>
        <Teleport to="body">
            <add-new-class
                :teachers="allTeachers.data"
                :teacher="!isSchoolAdmin() ? $page.props.auth.user : null"
                :teacherGrades="teacherGrades"
                class="modal"
                v-if="displayAddNewClassroom"
                @close="closeModal"
                @updateList="updateList"
                @save="onSaveClassroom"></add-new-class>
        </Teleport>
        <Teleport to="body">
            <classroom-info class="modal" :classroom="classroomInfo.classroom" :students="classroomInfo.students"
                :searchStudent="classroomInfo.searchStudent" :teacherGrades="classroomInfo.teacherGrades"
                v-if="displayClassroomInfoModal && classroomInfo" @close="closeClassroomInfo"
                @update="updateList"></classroom-info>
        </Teleport>

        <Teleport to="body">
            <add-new-teacher
                class="modal"
                v-if="displayAddNewTeacher"
                @close="displayAddNewTeacher = false"
                @save="onSaveTeacher"
            ></add-new-teacher>
        </Teleport>
        <Teleport to="body">
            <add-new-student
                class="modal"
                v-if="displayAddNewStudent"
                @close="displayAddNewStudent = false"
                @update="onSaveClassroom"
            ></add-new-student>
        </Teleport>
        <Teleport to="body">
            <ConfirmModal
                v-show=confirmModelJs.show.value
                @close="closeConfirm"
                @confirm="confirmDelete"
                cancel-button="Cancel"
                confirm-button="Confirm"
                title=""
            >
                <div>
                    <p> <b>{{ $t('V2.roster.delete_classroom_confirm.delete_label') }} </b></p>
                    <p>{{ $t('V2.roster.delete_classroom_confirm.delete_confirmation') }} </p>
                </div>
            </ConfirmModal>
        </Teleport>
    </JuniorHighlayout>
</template>

<script setup>
import Pagination from "@/Components/V2/Pagination.vue";
import JuniorHighlayout from "@/Layouts/V2/JuniorHighlayout.vue";
import AddNewStudent from "@/Components/V2/AddNewStudent.vue";
import AddNewTeacher from "@/Components/V2/AddNewTeacher.vue";
import AddNewClass from "@/Components/V2/AddNewClass.vue";
import { ChevronLeftIcon } from "lucide-vue-next";
import { onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import VueMultiselect from "vue-multiselect";
import { trans } from "laravel-vue-i18n";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import useAuthUserRole from "@/Composables/useAuthUserRole";
import RosterMenu from "@/Components/V2/Roster/RosterMenu.vue";
import ConfirmModal from "@/Components/V2/Modals/ConfirmModal.vue";
import { useModal } from "@/Composables/V2/Modal";
import LoadingSpinner from "@/Components/V2/LoadingSpinner.vue";
import axios from "axios";
import ClassroomInfo from "@/Components/V2/ClassroomInfo.vue";

const toast = useToast();
const confirmModelJs = useModal();

const props = defineProps([
    "classrooms",
    "requestData",
    "allTeachers",
    "teacherGrades",
    "timeline_options",
    "counts",
    "query"

]);
const { isSchoolAdmin } = useAuthUserRole();
const displayAddNewTeacher = ref(false);
const displayAddNewStudent = ref(false);
const displayAddNewClassroom = ref(false);

const openAddNewClassrrom = () => {
    displayAddNewClassroom.value = !displayAddNewClassroom.value;
};
const openAddNewStudent = () => {
    displayAddNewStudent.value = !displayAddNewStudent.value;
};
const openAddNewTeacher = () => {
    displayAddNewTeacher.value = !displayAddNewTeacher.value;
};
const isOpen = ref(false);

const searchClassroom = ref(props.requestData?.searchClassroom ?? "");
const isEditing = ref(false);
const selectAll = ref(false);
const classroomsEdit = ref(false);
const activeMenu = ref(null);
const teacherCount = ref([]);
const classroomCount = ref(0);
const studentCount = ref(0);
const gradeSelected = ref(props.requestData?.gradeSelected ?? "");
const classSelected = ref(props.requestData?.classSelected ?? "");
const isLoading=ref(false);
const displayClassroomInfoModal = ref(false);
const classroomInfo = ref();

const classroomOptions = [
    {
        name: "Archived",
        value: 1,
    },
    {
        name: "Unarchived",
        value: 0,
    },
];

//watchers
let isUpdating = false;

watch(
    () => gradeSelected.value,
    (oldValue, newValue) => {
        let queryObject = { ...props.query };
        if (newValue?.id != oldValue?.id) {
            findClassroom();
        }
        if(!oldValue) {
            delete queryObject["searchClassroom", "gradeSelected", "classSelected"];
            findClassroom();
        }
    },
);

function fetchClassroomInfo(classroomId, gradeName) {
    axios.get(`/teacher/classroom-info/${classroomId}`).then((response) => {
        classroomInfo.value = response.data;
        classroomInfo.value.classroom['grade_name'] = getGradeNumber(gradeName);
    });
    displayClassroomInfoModal.value = !displayClassroomInfoModal.value;
    classroomInfo.value = null;
}

const updateGradeSelected = () => {
    const assignedGrades = usePage().props.assignedGrades;
    if (gradeSelected.value) {
        const selectedOption = assignedGrades.find(option => option.id == gradeSelected.value.id);
        isUpdating = true;
        gradeSelected.value = selectedOption || null; // Reset if not found
        isUpdating = false;
    }
};

onMounted(() => {
    updateGradeSelected(); // Run this on component mount
});


const gradeWiseDataShow = (
    gradeIdArray,
    extraClassroomGradeArray,
    userActiveClassrooms,
    classroom
) => {
    const gradeNameArray = [];
    gradeIdArray.forEach((element) => {
        if (element.grade !== null) {
            //gradeNameArray.push(element.grade.name);
            var index = extraClassroomGradeArray.findIndex(
                (classroom) =>
                    classroom.grade_id == element.grade_id &&
                    classroom.archived == 0
            );

            if (
                extraClassroomGradeArray[index] &&
                element.grade.id == extraClassroomGradeArray[index].grade_id &&
                element.teacher_id == extraClassroomGradeArray[index].teacher_id
            ) {
                // Assuming you are iterating over the elements of extraClassroomGradeArray
                const matchingClassroom = extraClassroomGradeArray.find(
                    extraClassroom => extraClassroom.id === classroom.id
                );

                if (matchingClassroom) {
                    gradeNameArray.push(
                        matchingClassroom.extra_classroom_grade_id +
                        " (using " +
                        element.grade.name +
                        ")"
                    );
                }

            } else {
                // lets check for valid classroom first --task 165 elementary
                // filter valid classroom for teacher
                let isValidClassroom = userActiveClassrooms.filter(
                    (item) => item.teacher_id == element.teacher_id
                );
                let validGradeIds = []; // intialize validGradeIds variable and setting default value
                if (isValidClassroom.length > 0) {
                    isValidClassroom.forEach((item) =>
                        validGradeIds.push(item.grade_id)
                    ); // push valid gradeIds
                }
                if (validGradeIds.includes(element.grade_id)) {
                    gradeNameArray.push(element.grade.name); // add grade name if valid
                }
            }
        }
    });
    let uniqueSet = new Set();
    gradeNameArray.forEach((grade) => {
        uniqueSet.add(grade);
    });
    classroom['grade_name'] = Array.from(uniqueSet).join(", ");

    return Array.from(uniqueSet).join(", ");
}

const getGradeNumber = (gradeString) => {
    if (!gradeString) return "-";
    return gradeString.replaceAll("Grade ", "").replace("Kindergarten", "K");
};

// to show main menu
const showMenu = ref(false);

// close add new classroom modal
const closeModal = () => {
    router.reload({only:['classrooms']})
    displayAddNewClassroom.value = false;
    usePage().props.errors = {};
};

const resetButton = () => {
    gradeSelected.value = "";
    searchClassroom.value = "";
    findClassroom();
};

const classroomData = ref();

const closeClassroomInfo = () => {
    displayClassroomInfoModal.value = false;
    router.reload({ only: ['classrooms'] })

}
const openDeleteConfirm = (classroom) => {
    classroomData.value = classroom;
    confirmModelJs.showModal();
}

const closeConfirm = () => {
    confirmModelJs.hideModal();
}

const confirmDelete = () => {
    router.delete(route('teacher.classroom.destroy', classroomData.value.id), {
        onSuccess: (page) => {
            closeConfirm();
            if (page.props.flash.success !== null) {
                toast.success(page.props.flash.success);
            }
            if (page.props.flash.error !== null) {
                toast.error(page.props.flash.error);
            }
            updateList();
            router.reload({only:['counts']})
        },
    });
};

const updateList = () => {
    router.reload({only:['classrooms']})
}


// Event listeners for closing menus
if (typeof window !== "undefined") {
    window.addEventListener("click", (e) => {
        if (!e.target.closest(".relative")) {
            activeMenu.value = null;
            showMenu.value = false;
        }
    });
}

// Event listeners for closing menus
if (typeof window !== "undefined") {
    window.addEventListener("click", (e) => {
        if (!e.target.closest(".relative")) {
            activeMenu.value = null;
            showMenu.value = false;
        }
    });
}

const findClassroom = () => {
    router.on('start', () => {

        isLoading.value = true;

    });

    router.on('finish', () => {
        isLoading.value = false;

    });

       router.get(route("teacher.classroom-list"), {
            searchClassroom: searchClassroom.value,
            gradeSelected: gradeSelected.value,
            classSelected: classSelected.value,
        });

};


const modifyGradeName = (gradeName, id) => {
    if (gradeName.toLowerCase() === "kindergarten") {
        return "K";
    }
    return gradeName.replace(/grade\s*/i, "").trim();
};

const onSaveClassroom = () => {
    router.reload({
        only: ["classrooms"],
        onSuccess: () => {
            router.reload({only:['counts']})
        },
    })
};

const onSaveTeacher = () => {
    router.reload({
        only: ["allTeachers"],
        onSuccess: () => {
            router.reload({ only: ['counts'] })
        },
    })
};

// mount method
onMounted(() => {
    router.reload({ only: ['counts'] })
});

</script>

<style src="vue-multiselect/dist/vue-multiselect.css" scoped></style>

<style>
.modal {
    position: fixed;
    z-index: 999;
    top: 20%;
    left: 50%;
    width: 300px;
    margin-left: -150px;
}


</style>
