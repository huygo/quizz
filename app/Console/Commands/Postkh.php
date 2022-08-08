<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Khachhang;
use Elasticsearch\ClientBuilder;
class Postkh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postkh:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = ClientBuilder::create()
        ->setElasticCloudId('my_test:dXMtY2VudHJhbDEuZ2NwLmNsb3VkLmVzLmlvJDc3NDI3OTRmYjgwNzQxYzhiMzM4NTE0M2RkNTY0NjNmJGQ0OWE1OWE1MTE0OTQ4NWViNDk5MzJiMDBmNDVkYTNm')
        ->setBasicAuthentication('elastic', 'a4CL8ooyIv1jkjaXD0Yxqgsc')
        ->build();
        $list = Khachhang::where('id', '>',1000000)->where('id','<=',1021552)->get();
        $params = ['body' => []];
        foreach ($list as $key => $value) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'khachhang',
                    '_id'    => $value['id']
                ]
            ];
            $params['body'][] = [
                'name'     => $value['name'],
                'phone'     => $value['phone'],
                'email'     => $value['email'],
                'registrar'     => $value['registrar'],
                'created_date'     => date('Y-m-d\TH:i:s\Z', strtotime($value['created_date'])),
                'updated_date'     => date('Y-m-d\TH:i:s\Z', strtotime($value['updated_date'])),
                'status'     => $value['status'],
            ];
        }
        $responses = $client->bulk($params);
        echo "ok";
        return 0;
    }
}
