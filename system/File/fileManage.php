<table class="table">
	<tr>
		<td colspan="5"><a href="Index/file/<?php echo $prepath;?>">返回上一层</a></td>
	</tr>
	<tr>
		<td>名称</td>
		<td>大小</td>
		<td>类型</td>
		<td>修改时间</td>
		<td>操作</td>
	</tr>
	<?php foreach($data['filearray'] as $key => $value){ ?>
		<?php $current_path = $data['oldpath'] .$data['parsepath'] . '/' . $key; ?>
		<tr>
			<td>
				<?php echo is_dir($current_path) ? $key . '(目录)' : $key;?>
			</td>
			<td>
				<?php echo filesize($current_path);?>
			</td>
			<td>
				<?php echo is_dir($current_path) ? '目录' : '文件';?>
			</td>
			<td>
				<?php echo date('Y-m-d H:i:s' , filemtime($current_path));?>
			</td>
			<td>
				<?php echo is_dir($current_path) ? "<a href='Index/file/'" . $data['param'] . '@' . $key . ">进入目录</a> | " : ''; ?>
				<a href="">编辑</a> | 
				<a href="">删除</a></td>
		</tr>
	<?php } ?>
</table>