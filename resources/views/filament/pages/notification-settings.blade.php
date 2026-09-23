<x-filament-panels::page>
    <div x-data="{
        confirmMaintenance: false,
        confirm() {
            this.confirmMaintenance = true;
        },
        executeMaintenance() {
            this.confirmMaintenance = false;
            @this.toggleMaintenance();
        },
        init() {
            @this.on('confirm-maintenance-toggle', () => this.confirm());
        }
    }" class="lk-wrap">
        <x-filament::section>
            <x-slot name="heading">{{ __("settings.app_heading") }}</x-slot>
            <x-slot name="description">Status: {{ $maintenance ? __('settings.status_maintenance') : __('settings.status_live') }}.</x-slot>

            <x-filament::button wire:click="$dispatch('confirm-maintenance-toggle')" :color="$maintenance ? 'success' : 'danger'" icon="heroicon-o-wrench">
                {{ $maintenance ? __('settings.maintenance_disable') : __('settings.maintenance_enable') }}
            </x-filament::button>

            <!-- Confirmation Modal for Maintenance Toggle -->
            <div x-show="confirmMaintenance" x-transition x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="confirmMaintenance" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50"></div>
                    <div x-show="confirmMaintenance" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-xl p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                                <x-filament::icon icon="heroicon-o-wrench" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $maintenance ? __('settings.maintenance_disable') . '?' : __('settings.maintenance_enable') . '?' }}
                            </h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">
                            {{ $maintenance ? __('settings.maintenance_confirm_off') : __('settings.maintenance_confirm_on') }}
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="confirmMaintenance = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                {{ __('common.cancel') }}
                            </button>
                            <button type="button" @click="executeMaintenance" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">
                                {{ $maintenance ? __('settings.maintenance_yes_off') : __('settings.maintenance_yes_on') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __("settings.api_heading") }}</x-slot>
            <x-slot name="description">{{ __("settings.api_desc") }}</x-slot>

            <div class="lk-grid lk-grid-2">
                <div class="lk-field">
                    <label>{{ __("settings.new_token_name") }}</label>
                    <input wire:model="newTokenName" placeholder="cth: hp-kiosk-1" class="lk-input" />
                </div>
                <div class="lk-field">
                    <label>&nbsp;</label>
                    <x-filament::button wire:click="createApiToken" icon="heroicon-o-key">
                        {{ __('settings.create_token') }}
                    </x-filament::button>
                </div>
            </div>
            @if($newTokenPlain)
            <div class="lk-btnrow">
                <code class="lk-mono" style="user-select: all;">{{ $newTokenPlain }}</code>
            </div>
            @endif
            <div class="lk-tablewrap" style="margin-top: 1rem;">
                <table class="lk-table">
                    <thead>
                        <tr><th>{{ __("settings.th_name") }}</th><th>{{ __("settings.th_created") }}</th><th>{{ __("settings.th_last_used") }}</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($tokens as $token)
                        <tr>
                            <td>{{ $token->name }}</td>
                            <td>{{ $token->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $token->last_used_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><x-filament::button wire:click="revokeApiToken({{ $token->id }})" color="danger" size="sm">{{ __('settings.revoke') }}</x-filament::button></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="lk-empty">{{ __("settings.empty_tokens") }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __("settings.language_heading") }}</x-slot>
            <x-slot name="description">{{ __("settings.language_desc") }}</x-slot>

            <div class="lk-field" style="max-width: 22rem;">
                <label>{{ __("settings.language_label") }}</label>
                <select wire:model="form.app_locale" class="lk-input">
                    <option value="id">Bahasa Indonesia</option>
                    <option value="en">English</option>
                </select>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __("settings.schedule_heading") }}</x-slot>
            <x-slot name="description">{{ __("recap.daily_desc", ["time" => $scheduleTime, "count" => $unreturnedCount]) }}</x-slot>

            <x-filament::button wire:click="sendNow" icon="heroicon-o-paper-airplane">
                {{ __('recap.send_now') }}
            </x-filament::button>
        </x-filament::section>

        <form wire:submit="save">
            <x-filament::section>
                <x-slot name="heading">{{ __("settings.schedule_heading") }}</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check">
                        <input type="checkbox" wire:model="form.recap_enabled" />
                        <span>{{ __("settings.auto_daily") }}</span>
                    </label>
                    <div class="lk-field">
                        <label>{{ __("settings.send_time") }}</label>
                        <input type="time" wire:model="form.recap_time" class="lk-input" />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">{{ __("settings.gowa_heading") }}</x-slot>
                <x-slot name="description">{{ __("settings.gowa_desc") }}</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check lk-span">
                        <input type="checkbox" wire:model="form.gowa_enabled" />
                        <span>{{ __("settings.gowa_enable") }}</span>
                    </label>
                    <div class="lk-field lk-span">
                        <label>{{ __("settings.gowa_base") }}</label>
                        <input wire:model="form.gowa_base_url" placeholder="http://127.0.0.1:3000" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.basic_user") }} <span class="lk-opt">{{ __("settings.optional") }}</span></label>
                        <input wire:model="form.gowa_user" class="lk-input" />
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.basic_pass") }} <span class="lk-opt">{{ __("settings.optional") }}</span></label>
                        <input type="password" wire:model="form.gowa_pass" class="lk-input" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>{{ __("settings.target_label") }}</label>
                        <input wire:model="form.gowa_target" placeholder="6281234567890" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>{{ __("settings.relay_url") }} <span class="lk-opt">{{ __("settings.relay_url_hint") }}</span></label>
                        <input wire:model="form.gowa_relay_url" placeholder="http://100.64.0.10:8099" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field lk-span">
                        <label>{{ __("settings.relay_secret") }}</label>
                        <input type="password" wire:model="form.gowa_relay_secret" class="lk-input lk-mono" />
                    </div>
                </div>
                <div class="lk-btnrow">
                    <x-filament::button wire:click="testGowa" color="gray" icon="heroicon-o-signal">
                        {{ __('settings.test_conn') }}
                    </x-filament::button>
                    <input wire:model="testTarget" placeholder="{{ __('settings.test_target_placeholder') }}" class="lk-input lk-mono" style="max-width: 19rem;" />
                    <x-filament::button wire:click="sendTestWa" color="gray" icon="heroicon-o-chat-bubble-left-right">
                        {{ __('settings.send_test') }}
                    </x-filament::button>
                    @if($gowaTest)<span class="lk-note">{{ $gowaTest }}</span>@endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">{{ __("settings.webhook_heading") }}</x-slot>
                <x-slot name="description">{{ __("settings.webhook_desc") }}</x-slot>

                <div class="lk-grid lk-grid-2">
                    <label class="lk-check lk-span">
                        <input type="checkbox" wire:model="form.webhook_enabled" />
                        <span>{{ __("settings.webhook_enable") }}</span>
                    </label>
                    <div class="lk-field lk-span">
                        <label>{{ __("settings.webhook_url") }}</label>
                        <input wire:model="form.webhook_url" placeholder="https://..." class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.secret_how") }}</label>
                        <select wire:model="form.webhook_auth" class="lk-input">
                            <option value="header">{{ __("settings.opt_header") }}</option>
                            <option value="bearer">{{ __("settings.opt_bearer") }}</option>
                            <option value="basic">{{ __("settings.opt_basic") }}</option>
                            <option value="query">{{ __("settings.opt_query") }}</option>
                            <option value="none">{{ __("settings.opt_none") }}</option>
                        </select>
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.secret_token") }}</label>
                        <input wire:model="form.webhook_secret" class="lk-input lk-mono" />
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.basic_user_cond") }} <span class="lk-opt">{{ __("settings.basic_cond_hint") }}</span></label>
                        <input wire:model="form.webhook_user" class="lk-input" />
                    </div>
                    <div class="lk-field">
                        <label>{{ __("settings.basic_pass_plain") }}</label>
                        <input type="password" wire:model="form.webhook_pass" class="lk-input" />
                    </div>
                </div>
                <div class="lk-btnrow">
                    <x-filament::button wire:click="testWebhook" color="gray" icon="heroicon-o-signal">
                        {{ __('settings.test_webhook') }}
                    </x-filament::button>
                    @if($webhookTest)<span class="lk-note">{{ $webhookTest }}</span>@endif
                </div>
            </x-filament::section>

            <div style="display: flex; justify-content: flex-end;">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    {{ __('settings.save_all') }}
                </x-filament::button>
            </div>
        </form>

        <x-filament::section>
            <x-slot name="heading">{{ __("settings.history_heading") }}</x-slot>

            <div class="lk-tablewrap">
                <table class="lk-table">
                    <thead>
                        <tr><th>{{ __("settings.th_time") }}</th><th>{{ __("settings.th_channel") }}</th><th>{{ __("settings.th_target") }}</th><th>{{ __("settings.th_units") }}</th><th>{{ __("settings.th_status") }}</th><th>{{ __("settings.th_response") }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">{{ $log->created_at->format('d M H:i') }}</td>
                            <td>{{ $log->channel }}</td>
                            <td class="lk-mono">{{ $log->target }}</td>
                            <td>{{ $log->total }}</td>
                            <td>{{ $log->status }}</td>
                            <td class="lk-mono" style="color: #6b7280;">{{ \Illuminate\Support\Str::limit($log->response ?? '', 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="lk-empty">{{ __("settings.empty_logs") }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
