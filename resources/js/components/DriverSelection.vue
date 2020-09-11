<template>
    <div>
        <div class="flex mb-3">
            <label for="driver" class="w-1/3 my-auto">Driver</label>

            <div class="input-with-error flex-grow">
                <span v-if="errors.driver"
                      class="input-error">
                    {{ errors.driver[0] }}
                </span>

                <select id="driver" name="driver"
                        class="input appearance-none"
                        v-model="driver">
                    <option value="gcode">Gcode Driver</option>
                    <option value="dummy">Dummy Driver</option>
                </select>
            </div>
        </div>

        <div class="flex mb-3" v-show="isGcodeDriver">
            <label for="serial_port" class="w-1/3 my-auto">Serial Port</label>

            <div class="input-with-error flex-grow">
                <span v-if="errors.serial_port"
                      class="input-error">
                    {{ errors.serial_port[0] }}
                </span>

                <input id="serial_port" type="text" name="serial_port"
                       class="input"
                       v-bind:required="isGcodeDriver">
            </div>
        </div>

        <div class="flex mb-3" v-show="isGcodeDriver">
            <label for="baud_rate" class="w-1/3 my-auto">Baud Rate</label>

            <div class="input-with-error flex-grow">
                <span v-if="errors.baud_rate"
                      class="input-error">
                    {{ errors.baud_rate[0] }}
                </span>

                <input id="baud_rate" type="text" name="baud_rate"
                       class="input"
                       v-bind:required="isGcodeDriver">
            </div>
        </div>

        <div class="flex mb-3" v-show="isDummyDriver">
            <label for="delay" class="w-1/3 my-auto">Command Delay (sec)</label>

            <div class="input-with-error flex-grow">
                <span v-if="errors.delay"
                      class="input-error">
                    {{ errors.delay[0] }}
                </span>

                <input id="delay" type="text" name="delay"
                       class="input">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "DriverSelection",
        props: [
            'errors',
            'driver'
        ],
        computed: {
            isGcodeDriver: function () {
                return this.driver === "gcode";
            },
            isDummyDriver: function () {
                return this.driver === "dummy";
            }
        }
    }
</script>

<style scoped>

</style>