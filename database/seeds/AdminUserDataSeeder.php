<?php

use Illuminate\Database\Seeder;

use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

use App\Eloquents\Mysql\SAdminUser;

class AdminUserDataSeeder extends Seeder
{
    const CSV_FILENAME = '/../storage/seeds/admin.csv';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info( '[Start] 管理者リストのインポート.' );

        $config = new LexerConfig();
        $config->setDelimiter( "," );

        $lexer = new Lexer( $config );

        SAdminUser::truncate();

        $interpreter = new Interpreter();
        $interpreter->addObserver( function( array $row )
        {
            $objModel = new SAdminUser;
            $objModel->email           = $row[0];
            $objModel->password        = $row[1];
            $objModel->last_name       = $row[2];
            $objModel->first_name      = $row[3];
            $objModel->last_name_kana  = $row[4];
            $objModel->first_name_kana = $row[5];
            $objModel->level           = $row[6];
            $objModel->save();
        });
        $lexer->parse( app_path() . self::CSV_FILENAME, $interpreter );

        $this->command->info( '[End] 管理者リストのインポート.' );
    }
}
