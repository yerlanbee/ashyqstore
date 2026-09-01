<?php

namespace App\Filament\Resources\CameraResource\Pages;

use App\Filament\Resources\CameraResource;
use App\Infrastructure\Models\Camera;
use App\Infrastructure\Services\Contracts\EzvizServiceContract;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Throwable;

class ViewLive extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CameraResource::class;

    protected static string $view = 'filament.resources.camera-resource.pages.view-live';

    public Camera $record;

    public ?string $accessToken = null;

    public string $ezvizDomain;

    public ?string $liveUrl = null;

    public ?string $playbackUrl = null;

    public ?string $error = null;

    public ?array $data = [];

    public function mount(Camera $record): void
    {
        $this->record = $record;
        $this->ezvizDomain = rtrim((string) config('services.ezviz.url'), '/');

        $this->form->fill([
            'from' => now()->subHour()->format('Y-m-d H:i:s'),
            'to' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->loadLive();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('from')
                    ->label('Архив с')
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i')
                    ->native(false)
                    ->required()
                    ->maxDate(now()),

                DateTimePicker::make('to')
                    ->label('Архив по')
                    ->seconds(false)
                    ->displayFormat('d.m.Y H:i')
                    ->native(false)
                    ->required()
                    ->after('from')
                    ->maxDate(now()),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getHeading(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return $this->record->fridge?->name
            ? "Микромаркет: {$this->record->fridge->name} · Serial: {$this->record->serial}"
            : "Serial: {$this->record->serial}";
    }

    public function loadLive(): void
    {
        $this->playbackUrl = null;
        $this->error = null;

        try {
            $service = app(EzvizServiceContract::class);
            $this->accessToken = $service->getAccessToken();
            $this->liveUrl = $this->buildEzopenUrl('hd.live');
        } catch (Throwable $e) {
            $this->liveUrl = null;
            $this->error = $this->humanizeError($e);

            Notification::make()
                ->title('Не удалось получить live-поток')
                ->body($this->error)
                ->danger()
                ->send();
        }
    }

    public function loadPlayback(): void
    {
        $data = $this->form->getState();
        $this->error = null;

        try {
            $from = Carbon::parse($data['from']);
            $to = Carbon::parse($data['to']);

            if ($from->greaterThanOrEqualTo($to)) {
                throw new \InvalidArgumentException('Дата «от» должна быть раньше «до»');
            }

            $this->accessToken = app(EzvizServiceContract::class)->getAccessToken();
            $this->liveUrl = null;
            $this->playbackUrl = $this->buildEzopenUrl('cloud.rec.m3u8', $from, $to);

            $this->dispatch('player-source-changed', url: $this->playbackUrl);

            Notification::make()
                ->title('Архив загружен')
                ->success()
                ->send();
        } catch (Throwable $e) {
            $this->error = $this->humanizeError($e);

            Notification::make()
                ->title('Не удалось получить архив')
                ->body($this->error)
                ->danger()
                ->send();
        }
    }

    private function buildEzopenUrl(string $type, ?Carbon $from = null, ?Carbon $to = null): string
    {
        $code = $this->record->verification_code !== null
            ? strtoupper(trim($this->record->verification_code))
            : null;

        $auth = $code !== null && $code !== '' ? $code . '@' : '';
        $base = "ezopen://{$auth}open.ezviz.com/{$this->record->serial}/{$this->record->channel_no}.{$type}";

        if (str_contains($type, 'rec') && $from && $to) {
            $base .= '?begin=' . $from->format('YmdHis') . '&end=' . $to->format('YmdHis');
        }

        return $base;
    }

    public function backToLive(): void
    {
        $this->loadLive();
        $this->dispatch('player-source-changed', url: $this->liveUrl);
    }

    private function humanizeError(Throwable $e): string
    {
        $msg = $e->getMessage();

        // Усечём слишком длинный stack-trace из RequestException
        return mb_strlen($msg) > 300 ? mb_substr($msg, 0, 300) . '…' : $msg;
    }
}
