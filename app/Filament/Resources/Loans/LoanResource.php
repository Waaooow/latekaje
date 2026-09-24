<?php

namespace App\Filament\Resources\Loans;

use App\Filament\Resources\Loans\Pages\CreateLoan;
use App\Filament\Resources\Loans\Pages\EditLoan;
use App\Filament\Resources\Loans\Pages\ListLoans;
use App\Filament\Resources\Loans\Schemas\LoanForm;
use App\Filament\Resources\Loans\Tables\LoansTable;
use App\Models\Loan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $recordTitleAttribute = 'borrower_name';

    public static function getNavigationLabel(): string
    {
        return __('loans.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('loans.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('loans.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Akun peminjam pribadi (role users + identitas) hanya melihat
        // pinjamannya sendiri. Staf dan akun kiosk generik melihat semua.
        if ($user?->role === 'users' && ($user->code || $user->member_id)) {
            $query->where(function (Builder $w) use ($user) {
                if ($user->code) {
                    $w->orWhere('code', $user->code);
                }
                if ($user->member_id) {
                    $w->orWhere('member_id', $user->member_id);
                }
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return LoanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoans::route('/'),
            'create' => CreateLoan::route('/create'),
            'edit' => EditLoan::route('/{record}/edit'),
        ];
    }
}
