<x-filament-widgets::widget>
    <!-- The widget's contents (calendar and other elements) go here -->

    <!-- Modal Structure -->
    <div x-data="{
            showModal: @entangle('showModal'),
            modalDate: @entangle('modalDate'),
            modalHour: @entangle('modalHour'),
            teacherName: @entangle('teacherName'),
            absenceReason: @entangle('absenceReason')
        }"
        x-show="showModal"
        x-transition
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center z-50">

        <!-- Modal content -->
        <div class="bg-white p-8 rounded shadow-lg max-w-lg w-full">
            <div class="text-lg font-semibold">Absence Details</div>

            <!-- Modal Content (Date, Hour, Teacher, Reason) -->
            <div>
                <p><strong>Date:</strong> <span x-text="modalDate"></span></p>
                <p><strong>Hour:</strong> <span x-text="modalHour"></span></p>
                <div>
                    <label for="teacher">Teacher</label>
                    <input id="teacher" type="text" x-model="teacherName" disabled>
                </div>
                <div>
                    <label for="reason">Reason</label>
                    <textarea id="reason" x-model="absenceReason" disabled></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button x-on:click="showModal = false" class="bg-gray-300 text-black px-4 py-2 rounded">
                    Close
                </button>
                <!-- Add any buttons to handle Save or Edit actions -->
            </div>
        </div>
    </div>

</x-filament-widgets::widget>
