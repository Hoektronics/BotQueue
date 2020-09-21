<div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
    <div class="text-center text-xl bg-gray-200">Edit Bot</div>
    <div class="p-4">
        <form role="form" method="POST" action="{{ route('bots.update', [$this->bot]) }}">
            {{ csrf_field() }}
            {{ method_field('PATCH') }}

            <div class="flex mb-3 items-center">
                <label for="name" class="w-1/3 my-auto">Name</label>

                <div class="input-with-error flex-grow">
                    @if ($errors->has('name'))
                        <span class="input-error">{{ $errors->first('name') }}</span>
                    @endif

                    <input id="name" type="text" name="name"
                           wire:model="botName"
                           class="input"
                           required>
                </div>
            </div>

            <div class="flex mb-3 items-center">
                <label for="host" class="w-1/3 my-auto">Host</label>

                <div class="input-with-error flex-grow">
                    @if ($errors->has('host'))
                        <span class="input-error">{{ $errors->first('host') }}</span>
                    @endif

                    <select name="host" id="host"
                            class="input" wire:model="hostId">
                        @foreach($this->hosts as $host)
                            <option value="{{ $host->id }}">{{ $host->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex mb-3 items-center">
                <label for="driver" class="w-1/3 my-auto">Driver</label>

                <div class="input-with-error flex-grow">
                    @if ($errors->has('driver'))
                        <span class="input-error">{{ $errors->first('driver') }}</span>
                    @endif

                    <select name="driver" id="driver"
                            class="input" wire:model="driverType">
                        @foreach($this->drivers as $type => $name)
                            <option value="{{ $type }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($this->driverType == $this::GCODE_DRIVER)
                <div class="flex mb-3 items-center">
                    <label for="serial_port" class="w-1/3 my-auto">Serial Port</label>

                    <div class="input-with-error flex-grow">
                        @if ($errors->has('serial_port'))
                            <span class="input-error">{{ $errors->first('serial_port') }}</span>
                        @endif

                        <input id="serial_port" type="text" name="serial_port"
                               wire:model="serialPort"
                               class="input"
                               required>
                    </div>
                </div>

                <div class="flex mb-3 items-center">
                    <label for="baud_rate" class="w-1/3 my-auto">Baud Rate</label>

                    <div class="input-with-error flex-grow">
                        @if ($errors->has('baud_rate'))
                            <span class="input-error">{{ $errors->first('baud_rate') }}</span>
                        @endif

                        <input id="baud_rate" type="text" name="baud_rate"
                               wire:model="baudRate"
                               class="input"
                               required>
                    </div>
                </div>
            @endif

            @if($this->driverType == $this::DUMMY_DRIVER)
                <div class="flex mb-3 items-center">
                    <label for="command_delay" class="w-1/3 my-auto">Command Delay (sec)</label>

                    <div class="input-with-error flex-grow">
                        @if ($errors->has('command_delay'))
                            <span class="input-error">{{ $errors->first('command_delay') }}</span>
                        @endif

                        <input id="command_delay" type="text" name="command_delay"
                               wire:model="commandDelay"
                               class="input">
                    </div>
                </div>
            @endif

            <div class="flex mt-4 justify-end">
                <button type="submit" class="btn-blue btn-lg btn-interactive">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
