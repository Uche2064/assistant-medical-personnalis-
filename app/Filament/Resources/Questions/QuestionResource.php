<?php

namespace App\Filament\Resources\Questions;

use App\Enums\RoleEnum;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Filament\Resources\Questions\Pages\ViewQuestion;
use App\Filament\Resources\Questions\Schemas\QuestionForm;
use App\Filament\Resources\Questions\Schemas\QuestionInfolist;
use App\Filament\Resources\Questions\Tables\QuestionsTable;
use App\Models\Question;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $recordTitleAttribute = 'libelle';

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $modelLabel = 'Question';

    protected static ?string $pluralModelLabel = 'Questions';

    public static function getNavigationGroup(): ?string
    {
        return 'Questionnaires';
    }

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value);
    }

    public static function form(Schema $schema): Schema
    {
        return QuestionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestions::route('/'),
            'create' => \App\Filament\Resources\Questions\Pages\CreateQuestion::route('/create'),
            'view' => ViewQuestion::route('/{record}'),
            'edit' => \App\Filament\Resources\Questions\Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}

