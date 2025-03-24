<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h1 class="text-3xl font-bold text-base-content dark:text-white mb-6">Semester Setting</h1>

        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-primary text-lg py-3">
                    Semester: {{ displayedSemester.semester }}
                </span>
                <span class="badge badge-primary text-lg py-3">
                    Year: {{ displayedSemester.year }}
                </span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6 max-w-md">
            <div class="form-control">
                <label for="semester" class="label">
                    <span class="label-text dark:text-white">Semester:</span>
                </label>
                <select id="semester" v-model="form.semester" class="select select-bordered w-full">
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                </select>
            </div>
            <div v-if="form.errors.semester" class="text-sm mb-2 text-red-500 dark:text-red-500">
                {{ form.errors.semester }}
            </div>

            <div class="form-control">
                <label for="year" class="label">
                    <span class="label-text dark:text-white">Year:</span>
                </label>
                <select id="year" v-model="form.year" class="select select-bordered w-full">
                    <option v-for="year in years" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
            </div>
            <div v-if="form.errors.year" class="text-sm mb-2 text-red-500 dark:text-red-500">
                {{ form.errors.year }}
            </div>

            <button type="submit" class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
                Save
            </button>
        </form>
    </div>
</template>

<script>
import { reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

export default {
    props: {
        currentYear: Number,
        semester: {
            type: Object,
            default: null,
        },
    },
    setup(props) {
        const toast = useToast();
        const years = [props.currentYear - 1, props.currentYear];

        // Create a reactive object to hold the saved/displayed semester data.
        const displayedSemester = reactive({
            semester: props.semester ? props.semester.semester : '1st',
            year: props.semester ? props.semester.year : props.currentYear,
        });

        // Use the form object for the form data.
        const form = useForm({
            semester: displayedSemester.semester,
            year: displayedSemester.year,
        });

        const submit = () => {
            form.post('/settings', {
                onSuccess: () => {
                    // Only update the displayed values on a successful save.
                    displayedSemester.semester = form.semester;
                    displayedSemester.year = form.year;
                    toast.success("Semester added successfully!");
                },
                onError: () => {
                    toast.error("Error adding semester. Try again!");
                },
            });
        };

        return { form, years, submit, displayedSemester };
    },
};
</script>

<style scoped>
/* No additional dark mode styles required as dark mode is handled via Tailwind's dark: classes */
</style>
