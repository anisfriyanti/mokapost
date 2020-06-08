<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Users;
use Illuminate\Support\Str;
use DB;
use GuzzleHttp\Client;
class MokaController extends Controller
{

    private $client_id = "";
    private $secret = "";
    private $code = "";
    private $redirect_uri = "";
    private $baseurl = "";
    private $bussiness_id = "";
    private $headers = [];
    private $client = "";

    public function __construct()
    {
        $this->middleware('auth');
        $this->client_id = env("CLIENT_ID");
        $this->secret = env("CLIENT_SECRET");
        $this->code = env("CODE");
        $this->redirect_uri = env("REDIRECT_URI");
        $this->baseurl = env("BASE_URL_API");
        $this->bussiness_id = env("BUSINESS_ID");
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://api.mokapos.com']);

        $this->headers = ['Authorization' => 'Bearer ' . $this->gettokencurrent('access_token') , 'Accept' => 'application/json', ];

    }

    protected function refreshtoken()
    {

        $data = $this->requestrefresh();
        if ($data['code'] == 200)
        {
            $access_tokennew = $data['data']['access_token'];
            $refresh_token = $data['data']['refresh_token'];
            $expired_at = $data['data']['expires_in'];
            $created_at = $data['data']['created_at'];
            $access_tokenold = $this->gettokencurrent('refresh_token');

            $updatetoken = $this->updatetoken($access_tokenold, $access_tokennew, $refresh_token, $expired_at, $created_at);
            if ($updatetoken = true)
            {
                $token = $this->gettokencurrent("access_token");
                $output['access_token'] = $token;
                return response()->json($data, 200);

            }

        }
        else
        {
            return response()->json($data, $data['code']);
        }

    }

    public function requestrefresh()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseurl . "oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",

            CURLOPT_POSTFIELDS => array(
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->secret,
                'refresh_token' => $this->gettokencurrent('refresh_token') ,
                'redirect_uri' => $this->redirect_uri
            ) ,
        ));

        $response = curl_exec($curl);
        $coderespon = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data['code'] = $coderespon;
        $data['data'] = json_decode($response, true);
        return $data;

    }

    protected function requestoauth()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseurl . "oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->secret,
                'code' => $this->code,
                'redirect_uri' => $this->redirect_uri
            ) ,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response, true);
        return $data;

    }
    private function gettokencurrent($column)
    {
        $reserves = DB::table('moka')->value($column);
        return $reserves;
    }

    private function updatetoken($access_tokenold, $access_tokennew, $refresh_token, $expired_at, $created_at)
    {
        $result = DB::table('moka')->where('access_token', $access_tokenold)->update(['access_token' => $access_tokennew, 'refresh_token' => $refresh_token, 'expired_at' => $expired_at, 'created_at' => $created_at, ]);

        return $result;
    }

    public function requestbussiness()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mokapos.com/v1/businesses",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->gettokencurrent('access_token')
            ) ,
        ));

        $response = curl_exec($curl);
        $data['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        $data = json_decode($response, true);
        return $data;
    }

    public function requestoutlets()
    {

        $response = $this
            ->client
            ->request('GET', '/v1/businesses/' . $this->bussiness_id . '/outlets?per_page=500', ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;
    }

    public function gettransaction()
    {

        $response = $this
            ->client
            ->request('GET', '/v2/outlets/127260/reports/get_latest_transactions', ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;

    }
    public function itemsdata($id_outlet)
    {
        $response = $this
            ->client
            ->request('GET', '/v1/outlets/'.$id_outlet.'/items?per_page=1000', ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;

    }
    public function generatetransaction($since,$until,$id_outlet)
    {
        $response = $this
            ->client
            ->request('GET', '/v2/outlets/'.$id_outlet.'/reports/get_latest_transactions/?until='.$until.'&per_page=1000&since='.$since, ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;
    }
    public function generateitems($outlet_id)
    {
        $response = $this
            ->client
            ->request('GET', '/v1/outlets/' . $outlet_id . '/items?per_page=1000', ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;

    }
    public function generatesummaryitems($outlet_id)
    {
        $response = $this
            ->client
            ->request('GET', '/v3/outlets/' . $outlet_id . '/reports/item_sales?start=01/01/2020&end=30/01/2020&per_page=500', ['headers' => $this->headers]);
        $data = $response->getBody()
            ->getContents();
        $output = json_decode($data, true);
        return $output;

    }
    public function callGenerateSummary($outlet_id, $start, $end)
    {
        //   $outlets = array(
        // '127596','66900','127255','36856','36913','130427','69839','58287','127258','127637','69836','37143','127992','70577','63442','56562','69838','37123','68836','60538','70575','21901','127239','68837','265004','127263','259809','127234','71496','69837','68835','68834','67913','67912','67911','67910','65825','64834','58264','57837','56960','56563','55556','54200','52504','52500','51679','49859','49856','49265','48532','44099','44097','43901','43263','42397','41755','41752','40533','39832','39453','39452','37449','37127','37125','36853','36822','36816','36695','28781','27033','25839','25838','24284','24283','24248','22752','22750','20759','18558','127961','127250','127247','127204','127199','127196','127193','126911','27032','24281','24280','24249','24247','23321','23320','22751','21902','19865','18559','17278'
        //   );
        // $outlets= \DB::table('outlets')->select('id_outlet')->paginate(50);
        // $datas = array();
        // foreach ($outlets as $key => $value) {
        //   $datasOutlet = $outlets->data;
        //   $data = $this->generatesummarysales($outlets->data[?]->id_outlet, $start, $end);
        //   array_push($datas, $data);
        // }
        $datas = array();
        for ($i = 0;$i < count($outlets);$i++)
        {
            $data = $this->generatesummarysales($outlets[$i], $start, $end);
            array_push($datas, $data);
        }
        return $datas;
    }
    public function generatesummarysales($outlet_id, $start, $end)
    {
        //    $response = $this->client->request('GET', '/v2/outlets/'.$outlet_id.'/reports/sales_summary?start='.$start.'&end='.$end, [
        //         'headers' => $this->headers
        //     ]);
        // $data = $response->getBody()->getContents();
        //  $output= json_decode($data, true);
        //    return $output;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mokapos.com/v2/outlets/' . $outlet_id . '/reports/sales_summary?start=' . $start . '&end=' . $end,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->gettokencurrent('access_token')
            ) ,
        ));

        $response = curl_exec($curl);
        $data['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        $data = json_decode($response, true);
        return $data;

    }

}

