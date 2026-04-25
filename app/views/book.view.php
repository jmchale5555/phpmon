<?php include 'partials/header.view.php' ?>

<p class="text-center mb-6 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 xl:px-48 dark:text-gray-400">Welcome <?= $name ?></p>
<div x-data="bookingForm">
    <div x-show="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-cloak>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-sm w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Cancellation</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Are you sure you want to cancel this booking?</p>
            <div class="flex justify-end space-x-3">
                <button @click="showDeleteModal = false"
                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    Cancel
                </button>
                <button @click="confirmDelete()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
    <form @submit.prevent="submitForm">
        <!-- Parent component with shared state -->
        <div x-data="{ 
        rooms: <?php echo htmlspecialchars($rooms) ?>,
        dates: <?php echo htmlspecialchars($dates) ?>,
        desks: [],
        bookings: [],
        selectedRoomId: null,
        selectedDeskId: null,
        cancelButton: false,

        async handleGetDesksAndBookings(roomId) {
            this.selectedRoomId = roomId;

            await this.getDesks(roomId);
            await this.getBookings(roomId);
        },

        async getDesks(roomId) {
            const response = await fetch(`book/getDeskByRoomId/${roomId}`);
            this.desks = await response.json();
            if (this.desks.length > 0) {
                this.selectedDeskId = this.desks[0].id;
            } else {
                this.selectedDeskId = '223';
            }
        },

        async getBookings(roomId) {
                try {
                    const response = await fetch(`book/getBookingsByRoomId/${roomId}`);
                    if (!response.ok) {
                        throw new Error('Failed to fetch bookings');
                    }
                    const freshBookings = await response.json();
                    this.bookings = freshBookings || []; // Ensure bookings is always an array
                } catch (error) {
                    console.error('Error fetching bookings:', error);
                    this.message = 'Error refreshing booking data';
                    this.hasError = true;
                    this.bookings = []; // Reset to empty array on error
                }
            },

        parseDate(value) {
            const datePart = value.split('<br>')[1];
            return datePart.replace('+', '');
        },

        parseDateUnix(value) {
            const datePart = value.split('<br>')[1];
            const datePart2 = datePart.replace('+', '');
            const [day, month, year] = datePart2.split('-');
            const reformattedDate = `${year}-${month}-${day}`;
            const dateObj = new Date(reformattedDate);
            const thisUnixDate = Math.floor(dateObj.getTime() / 1000);

            return thisUnixDate;            
        },

        isSlotBooked(date) {
            if(this.selectedRoomId === '...') {
                // do something
            }
            if (!this.selectedRoomId || !this.selectedDeskId || !this.bookings) {
                return false;
            }
            return this.bookings.some(booking => 
                booking.room_id == this.selectedRoomId &&
                booking.desk_id == this.selectedDeskId &&
                booking.res_date === date
            );
        },
        getBookingUserName(date) {
            const thisBooking = this.bookings.find(b => 
                b.res_date===date && 
                b.room_id==this.selectedRoomId && 
                b.desk_id==this.selectedDeskId
            );
            return thisBooking ? thisBooking.user_name : 'No Booking' ;
        },

        getBookingId(date) {
            const thisBooking = this.bookings.find(b => 
                b.res_date===date && 
                b.room_id==this.selectedRoomId && 
                b.desk_id==this.selectedDeskId
            );
            return thisBooking ? thisBooking.id : 'No Booking' ;
        },


        }" class="flex flex-row space-x-3">

            <!-- Room and Desk Selection -->
            <div class="w-2/5 gap-1 mb-6 bg-slate-200 dark:bg-slate-800 p-6 rounded-lg">
                <!-- Rooms dropdown -->
                <div>
                    <label for="room" class="dark:text-gray-400">Select Room:</label>
                    <select
                        id="room"
                        name="room_id"
                        @change="handleGetDesksAndBookings($event.target.value)"
                        @refresh-room-data.window="getBookings($event.detail.roomId)"
                        x-model="formData.room_id"
                        class="flex flex-auto z-10 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 bg-gray-300 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 focus:ring-1 focus:ring-purple-700">
                        <template x-for="room in rooms" :key="room.id">
                            <option :value="room.id" type="option" id="room" name="room_id" class="block py-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" x-text="room.room_name"></option>
                        </template>
                    </select>
                </div>

                <!-- Desks dropdown -->
                <div>
                    <label for="desk" class="dark:text-gray-400">Select Desk:</label>
                    <select
                        id="desk"
                        name="desk_id"
                        @change="selectedDeskId = $event.target.value"
                        x-model="formData.desk_id"
                        class="flex z-10 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:text-gray-300 focus:ring-1 focus:ring-purple-700">
                        <template x-for="desk in desks" :key="desk.id">
                            <option :value="desk.id" type="option" id="desk" name="desk_id" class="block px-4 py-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-purple-600 dark:hover:text-white" x-text="desk.desk_number"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Date picker -->
            <div x-show="selectedDeskId">
                <div x-show="selectedDeskId < 222" class="col-span-3 flex flex-wrap bg-slate-200 dark:bg-slate-700 shadow-md md:justify-center mx-auto py-4 px-2 rounded-lg">
                    <template x-for="value in dates">
                        <div class="flex outline group hover:bg-slate-300 dark:hover:bg-slate-600 hover:shadow-lg hover-dark-shadow rounded-lg mx-8 my-2 transition-all duration-300 cursor-pointer justify-center w-28"
                            :class="{ 'bg-indigo-900 hover:!bg-indigo-900': isSlotBooked(parseDateUnix(value)) }">
                            <span class="cursor-auto">
                                <div class="text-center font-bold">
                                    <div class="break-normal"></div>
                                    <div class="text-xs text-gray-800 dark:text-gray-200">
                                        <template x-if="!isSlotBooked(parseDateUnix(value))">
                                            <p class="py-3" x-html="value"></p>
                                        </template>
                                        <template x-if="isSlotBooked(parseDateUnix(value))">
                                            <div>
                                                <p class=" text-blue-300 text-sm mt-3">Booked by</p>
                                                <p class="span text-blue-300 text-sm " x-html="getBookingUserName(parseDateUnix(value))"></p>
                                                <template x-if="getBookingUserName(parseDateUnix(value)) == '<?= $_SESSION['USER']->name ?>'  ? true : false ">
                                                    <button type="button" @click="openDeleteModal(getBookingId(parseDateUnix(value)))" class="mt-3">
                                                        <span class="!text-red-500 text-xs">Cancel</span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!isSlotBooked(parseDateUnix(value))" class="mb-2">
                                            <input
                                                type="radio"
                                                :value="parseDateUnix(value)"
                                                id="res_date"
                                                name="res_date"
                                                x-model="formData.res_date"
                                                class="mb-2 w-5 h-5 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        </template>
                                    </div>
                                </div>
                            </span>
                        </div>
                    </template x-show="formData.res_date !== '' ">
                    <div class="grid grid-rows-2">
                        <button :disabled="isSubmitting" class="grid row-span-1 flex float-right min-w-max text-white !bg-gradient-to-br !from-purple-600 !to-blue-500 !hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2 text-center my-3 me-2 mb-2">
                            <span x-show="!isSubmitting">Submit</span>
                            <span x-show="isSubmitting">Saving...</span>
                        </button>
                        <template x-if="message" class="grid row-span-1">
                            <div class="grid grid-row" x-init="setTimeout(() => message = '', 3000)"
                                :class="{'text-green-500': !hasError, 'text-red-500': hasError}"
                                x-text="message">
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingForm', () => ({
            formData: {
                room_id: "",
                desk_id: "",
                res_date: "",
            },
            errors: {},
            message: '',
            hasError: false,
            isSubmitting: false,
            showDeleteModal: false,
            bookingToDelete: null,
            bookings: [],

            openDeleteModal(bookingId) {
                this.bookingToDelete = bookingId;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                try {
                    await this.deleteBooking(this.bookingToDelete);
                    this.showDeleteModal = false;
                    this.bookingToDelete = null;
                    this.message = 'Booking cancelled successfully!';
                    this.hasError = false;

                    // Refresh the bookings data after successful deletion
                    if (this.selectedRoomId) {
                        await this.getBookings(this.selectedRoomId);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.message = 'An error occurred while cancelling the booking.';
                    this.hasError = true;
                }
            },

            async deleteBooking(bookingId) {
                const response = await fetch(`book/deleteBookingById/${bookingId}`);
                if (!response.ok) {
                    throw new Error('Failed to delete booking');
                }
                const result = await response.json();

                // Use the room_id from formData to fetch updated bookings
                if (this.formData.room_id) {
                    this.$dispatch('refresh-room-data', {
                        roomId: this.formData.room_id,
                    });
                }

                // Immediately update local state for UI responsiveness
                if (Array.isArray(this.bookings)) {
                    this.bookings = this.bookings.filter(booking => booking.id !== bookingId);
                }

                // Fetch fresh data using getBookings
                return result;
            },

            async submitForm() {
                this.isSubmitting = true;
                this.errors = {};
                this.message = '';
                if (!this.formData.res_date) {
                    this.hasError = true;
                    this.message = 'Please select a date';
                    this.isSubmitting = false;
                    return;
                }
                try {
                    const response = await fetch('book/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            // Add CSRF token if needed
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        if (result.errors) {
                            this.errors = result.errors;
                        }
                        this.hasError = true;
                        this.message = result.message || 'An error occurred';
                        return;
                    }

                    // Use the room_id from formData to fetch updated bookings
                    if (this.formData.room_id) {
                        this.$dispatch('refresh-room-data', {
                            roomId: this.formData.room_id,
                        });
                    }

                    this.hasError = false;
                    this.message = 'Booking saved successfully!';


                } catch (error) {
                    console.error('Error:', error);
                    this.hasError = true;
                    this.message = 'An error occurred while saving the booking.';
                } finally {
                    this.isSubmitting = false;
                }
            },

        }));
    });
</script>

<?php include 'partials/footer.view.php' ?>