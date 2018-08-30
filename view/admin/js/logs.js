$(function () {
	$.getJSON('logs.php?type=json',function (data) {
		console.log(data);
	})
	// $('#tbody_data').html('                                    <tr>
 //                                        <th>{$value['id']}</th>
 //                                        <th>{$value['ip']}</th>
 //                                        <th>{$value['ua']}</th>
 //                                        <th><a href="../{$value['path']}">{$value['path']}</a> </th>
 //                                        <th>{$value['size']}</th>
 //                                        <th>{$value['level']}</th>
 //                                        <th>{$value['see']}</th>
 //                                    </tr>
 //                                    <tfoot>
 //                                    <tr>
 //                                    <td colspan="7">
 //                                    <div class="pagination-info pull-left">共有{$data['count']}条，每页显示：50条</div>
 //                                    <div class="pull-right king-page-box">
 //                                        <ul class="pagination pagination-small pull-right">
 //                                          <li class="disabled"><a>«</a></li>
 //                                          <li class="active"><a>1</a></li>
 //                                          <li><a href="javascript:void(0);">2</a></li>
 //                                          <li><a href="javascript:void(0);">3</a></li>
 //                                          <li><a href="javascript:void(0);">4</a></li>
 //                                          <li><a href="javascript:void(0);">»</a></li>
 //                                        </ul>
 //                                    </div>
 //                                    </td>
 //                                    </tr>
 //                                    </tfoot>');
});