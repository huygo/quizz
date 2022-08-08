@foreach($petani as $key => $data)
    <tr>    
      <th>{{$data->id}}</th>
      <th>{{$data->name}}</th>           
    </tr>
@endforeach