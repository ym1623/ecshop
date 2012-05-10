<div class="share">
<ul class="clearfix">
<li style="padding:0 10px 0 12px;">分享到:</li>
<li><a href="#" class="deal-share-im im" onclick="javascript:return false;">MSN/QQ</a></li>
<li><a target="_blank" href="http://www.kaixin001.com/repaste/share.php?rurl=<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>&amp;rtitle=<?php echo $this->_var['group_shopname']; ?>&amp;rcontent=<?php echo $this->_var['group_buy']['group_name']; ?>" class="kaixin">开心</a></li>
<li><a target="_blank" href="http://share.renren.com/share/buttonshare.do?link=<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>&amp;title=<?php echo $this->_var['group_shopname']; ?>：<?php echo $this->_var['group_buy']['group_name']; ?>" class="renren">人人</a></li>
<li><a target="_blank" href="http://www.douban.com/recommend/?url=<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>&amp;title=<?php echo $this->_var['group_shopname']; ?>：<?php echo $this->_var['group_buy']['group_name']; ?>" class="douban">豆瓣</a></li>
<li><a target="_blank" href="http://v.t.sina.com.cn/share/share.php?url=<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>&amp;title=<?php echo $this->_var['group_shopname']; ?>：<?php echo $this->_var['group_buy']['group_name']; ?>" class="sina">新浪微博</a></li>
<li><a href="mailto:?subject=今天的产品是：<?php echo $this->_var['group_buy']['group_name']; ?>。<br />来看看吧：<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>" class="email">邮件</a></li>
<li><a href="<?php echo $this->_var['group_buy']['invite_url']; ?>" class="fanli">邀请返利 <strong class="c1"><?php echo $this->_var['group_buy']['formated_goods_rebate']; ?></strong> 元</a></li>
</ul>
<div class="deal-share-im-b clearfix" style="display:none;">
<h3>复制下面的内容后通过 MSN 或 QQ 发送给好友：</h3>
<p><input type="text" id="share-copy-text" class="txt lf" size="65" value="<?php echo $this->_var['ecgweburl']; ?><?php echo $this->_var['group_buy']['url']; ?>" onclick="this.focus();this.select();" style="color:#999;" />
</p>
</div>
</div>