<template>
    <div>
        <div class="flex mb-3">
            <label for="driver" class="w-1/3 my-auto">Driver</label>

            <div class="input-with-error flex-grow">
                <select id="driver" name="driver"
                class="flex-grow select-all"
                v-model="selected">
                    <option value="printrun">Printrun Driver</option>
                    <option value="dummy">Dummy Driver</option>
                </select>
            </div>
        </div>

        <div class="flex mb-3" v-show="selected === 'printrun'">
            <label for="serial_port" class="w-1/3 my-auto">Serial Port</label>

            <div class="input-with-error flex-grow">
                <input id="serial_port" type="text" name="serial_port"
                       class="input">
            </div>
        </div>

        <div class="flex mb-3" v-show="selected === 'printrun'">
            <label for="baud_rate" class="w-1/3 my-auto">Baud Rate</label>

            <div class="input-with-error flex-grow">
                <input id="baud_rate" type="text" name="baud_rate"
                       class="input">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "DriverSelection",
        data: function() {
            return {
                selected: "printrun"
            }
        }
    }
</script>

<style scoped>

</style>