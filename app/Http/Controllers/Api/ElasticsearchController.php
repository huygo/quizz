<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Elasticsearch\ClientBuilder;
class ElasticsearchController extends Controller
{
    public function __construct()
    {
        $this->client = ClientBuilder::create()
        ->setElasticCloudId('my_test:dXMtY2VudHJhbDEuZ2NwLmNsb3VkLmVzLmlvJDc3NDI3OTRmYjgwNzQxYzhiMzM4NTE0M2RkNTY0NjNmJGQ0OWE1OWE1MTE0OTQ4NWViNDk5MzJiMDBmNDVkYTNm')
        ->setBasicAuthentication('elastic', 'a4CL8ooyIv1jkjaXD0Yxqgsc')
        ->build();
    }
    // Tạo mới 1 index
    public function createindex(){
        $params = [
            'index' => 'accounts'
        ];
        try {
            $response = $this->client->indices()->create($params);
            return response()->json([
                'code'=>200,
                'success' => true,
                'message' => $response
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => 'index already exists'
            ], 400);
        }
    }

    public function put_mapping (){
        $params = [
            'index' => 'accounts',
            'body' => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'login_id' => [
                        'type' => 'text',
                        'analyzer' => 'standard'
                    ],
                    'email' => [
                        'type' => 'text',
                        'analyzer' => 'keyword'
                    ],
                    'account_type' => [
                        'type' => 'integer',
                    ],
                    'created_date' => [
                        'type' => 'date',
                    ],
                    'updated_date' => [
                        'type' => 'date',
                    ],
                ]
            ]
        ];
        try {
            $response = $this->client->indices()->putMapping($params);
            return response()->json([
                'code'=>200,
                'success' => true,
                'message' => $response
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function getid(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
            'index' => 'bail|required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $data = $request->only(['id', 'index']);
        $params = [
            'index' => $data['index'],
            'id'    => $data['id']
        ];
        try {
            $response = $this->client->get($params);
            return response()->json([
                'code'=>$this->statusCode,
                'success' => true,
                'message' => $this->message,
                'data' =>$response['_source']
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }
    
    // tìm kiếm theo ngày
    public function searchaccount(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $data = $request->all();
        $params = [
            'index' => 'accounts,khachhang',
            'body' => [
                'from'=>0,'size' => 10,
                'sort' => [
                    'created_date' => [
                        'order' => 'desc'
                    ]
                ],
                'query' => [
                    'range' => [
                        'created_date' => [
                            'time_zone' => '+01:00',
                            'gte' => date('Y-m-d\TH:i:s\Z', strtotime($data['start_date'])),
                            'lte' => date('Y-m-d\TH:i:s\Z', strtotime($data['end_date'])),
                        ],
                    ],
                ],
            ],
        ];

        // $params = [
        //     'index' => 'accounts,khachhang',
        //     'size' => 100,
        //     'body' => [
        //         'query' => [
        //             "bool" => [
        //                 'should' => [
        //                     // [
        //                     //     'match' => [
        //                     //         [ 'email' => 'duongvanbinh1305@gmail.com' ]
        //                     //     ]
        //                     // ],
        //                     [
        //                         'multi_match' => [
        //                             'query' => 'duongvanbinh1305@gmail.com',
        //                             'fuzziness' => 'AUTO',
        //                             "prefix_length" => 0,
        //                             "max_expansions" => 100,
        //                             'fields' => ['email'],
        //                         ]
        //                     ] 
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];

        // $params = [
        //     'body' => [
        //         'query' => 'SELECT email FROM accounts  LIMIT 10'
        //     ]
        // ];
        
        try {
            // $response = $this->client->sql()->query($params);
            $response = $this->client->search($params);
            return response()->json([
                'code'=>$this->statusCode,
                'success' => true,
                'message' => $this->message,
                'data' =>$response
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    // đếm kết quả tìm kiếm
    public function dem(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $data = $request->all();
        $params = [
            'index' => 'khachhang',
            'body' => [
                'query' => [
                    'range' => [
                        'created_date' => [
                            'time_zone' => '+01:00',
                            'gte' => date('Y-m-d\TH:i:s\Z', strtotime($data['start_date'])),
                            'lte' => date('Y-m-d\TH:i:s\Z', strtotime($data['end_date'])),
                        ],
                    ],
                ],
            ],
            ];
        try {
            $response = $this->client->count($params);
            return response()->json([
                'code'=>$this->statusCode,
                'success' => true,
                'message' => $this->message,
                'data' =>$response
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function updatekhachhang(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
            'created_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $data = $request->all();
        $params = [
            'index' => 'khachhang',
            'id'    => $data['id'],
            'body'  => [
                'doc' => [
                    'created_date' => date('Y-m-d\TH:i:s\Z', strtotime($data['created_date'])),
                ]
            ]
        ];
        try {
            $response = $this->client->update($params);
            return response()->json([
                'code'=>$this->statusCode,
                'success' => true,
                'message' => $this->message,
                'data' =>$response
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function search(Request $request){
        $email = $request->only('email');
        $validator = Validator::make($email, [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $params = [
            'index' => 'khachhang',
            'body'  => [
                'query' => [
                    'match_phrase_prefix' => [
                        'email' => $email['email']
                    ]
                ],
                'size' => 200
            ]
        ];

        // fill all
        // $params = [
        //     'from'=> 0,
        //     'size'   => 50,          
        //     'index'  => 'khachhang',
        //     'body'   => [
        //         'query' => [
        //             'match_all' => new \stdClass()
        //         ],
        //         'sort' => [
        //             'created_date' => [
        //                 'order' => 'desc'
        //             ]
        //         ]
        //     ]
        // ];
        $response = $this->client->search($params);
        // $response = $client->search($params);
        return response()->json($response, 200);
    } 
}
