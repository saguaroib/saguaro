<?php
define(S_HOME, 'In&#237;cio');											//Forwards to home page
define(S_ADMIN, 'Gerenciar');										//Forwards to Management Panel
define(S_RETURN, 'Retornar');										//Returns to image board
define(S_POSTING, 'Tipo de postagem: Resposta');								//Prints message in red bar atop the reply screen
define(S_NOTAGS, 'Tags HTML est&#227;o liberadas.');								//Prints message on Management Board
define(S_NAME, 'Nome');											//Describes name field
define(S_EMAIL, 'E-mail');										//Describes e-mail field
define(S_SUBJECT, 'T&#243;pico');										//Describes subject field
define(S_SUBMIT, 'Enviar');										//Describes submit button
define(S_COMMENT, 'Coment&#225;rio');										//Describes comment field
define(S_UPLOADFILE, 'Arquivo');										//Describes file field
define(S_NOFILE, 'Sem arquivo');										//Describes file/no file checkbox
define(S_DELPASS, 'Senha');										//Describes password field
define(S_DELEXPL, '(Senha para deletar arquivo)');							//Prints explanation for password box (to the right)
define(S_RULES, '<ul><li>Tipos de arquivos suportados s&#227;o: GIF, JPG, PNG</li>
<li>Tamanho m&#225;ximo de arquivo &#233; '.MAX_KB.' KB.</li>
<li>Imagens maiores que '.MAX_W.'x'.MAX_H.' ser&#227;o amostradas.</li>
<li>Imagens menores que '.MIN_W.'x'.MIN_H.' ser&#227;o recusadas.</li>
</ul>');				//Prints rules under posting section
define(S_REPORTERR, 'Erro: N&#227;o foi possivel achar respota.');							//Returns error when a reply (res) cannot be found
define(S_THUMB, 'Amostra exibida, clique na imagem para o tamanho cheio.');					//Prints instructions for viewing real source
define(S_PICNAME, 'Arquivo:');										//Prints text before upload name/link
define(S_REPLY, 'Responder');										//Prints text for reply link
define(S_OLD, 'Marcado para ser deletado (antigo).');								//Prints text to be displayed before post is marked for deletion, see: retention
define(S_RESU, '');											//Prints post?
define(S_ABBR, ' postagens omitidas. Clique em responder para ver.');						//Prints text to be shown when replies are hidden
define(S_REPDEL, 'Deletar post ');									//Prints text next to S_DELPICONLY (left)
define(S_DELPICONLY, 'Arquivo apenas');									//Prints text next to checkbox for file deletion (right)
define(S_DELKEY, 'Senha ');										//Prints text next to password field for deletion (left)
define(S_DELETE, 'Deletar');										//Defines deletion button's name
define(S_PREV, 'Anterior');										//Defines Anterior button
define(S_FIRSTPG, 'Anterior');										//Defines previous button
define(S_NEXT, 'Pr&#243;ximo');											//Defines next button
define(S_LASTPG, 'Pr&#243;ximo');										//Defines next button
define(S_FOOT, '- <a href="http://www.2chan.net/" target="_blank">futaba</a> + <a href="http://1chan.net/futallaby/" target="_blank">futallaby</a> + <a href="http://saguaroimgboard.tk/" target="_blank">saguaro 0.98.3b4</a> -'); //Prints footer (leave these credits)
define(S_RELOAD, 'Retornar');										//Reloads the image board (refresh)
define(S_UPFAIL, 'Erro: Upload falhou.');								//Returns error for failed upload (reason: unknown?)
define(S_NOREC, 'Erro: N&#227;o foi poss&#237;vel achar registro.');								//Returns error when record cannot be found
define(S_SAMEPIC, 'Erro: Soma de verifica&#231;&#227;o md5 detectada.');						//Returns error when a md5 checksum dupe is detected
define(S_TOOBIG, 'Essa imagem &#233; muito larga! Envie algo menor!');
define(S_TOOBIGORNONE, 'Ou essa imagem &#233; muito grande ou n&#227;o tem nenhuma imagem.');
define(S_UPGOOD, ' '.$upfile_name.' enviado!<br><br>');					//Defines message to be displayed when file is successfully uploaded
define(S_STRREF, 'Erro: Cadeia recusada.');								//Returns error when a string is refused
define(S_UNJUST, 'Erro: N&#227;o foi possivel achar registro.');								//Returns error on an unjust POST - prevents floodbots or ways not using POST method?
define(S_NOPIC, 'Erro: Nenhum arquivo detectado.');								//Returns error for no file selected and override unchecked
define(S_NOTEXT, 'Erro: Nenhum texto colocado.');								//Returns error for no text entered in to subject/comment
define(S_MANAGEMENT, 'Gerenciador : ');									//Defines prefix for Manager Post name
define(S_DELETION, 'Deletar');										//Prints deletion message with quotes?
define(S_TOOLONG, 'Error: Espa&#231;o longo demais.');								//Returns error for too many characters in a given field
define(S_UNUSUAL, 'Erro: Resposta anormal.');								//Returns error for abnormal reply? (this is a mystery!)
define(S_BADHOST, 'Erro: Host est&#225; banido.');								//Returns error for banned host ($badip string)
define(S_PROXY80, 'Erro: Proxy detectado em :80.');							//Returns error for proxy detection on port 80
define(S_PROXY8080, 'Erro: Proxy detectado em :8080.');							//Returns error for proxy detection on port 8080
define(S_SUN, 'Dom');											//Defines abbreviation used for "Sunday"
define(S_MON, 'Seg');											//Defines abbreviation used for "Monday"
define(S_TUE, 'Ter');											//Defines abbreviation used for "Tuesday"
define(S_WED, 'Qua');											//Defines abbreviation used for "Wednesday"
define(S_THU, 'Qui');											//Defines abbreviation used for "Thursday"
define(S_FRI, 'Sex');											//Defines abbreviation used for "Friday"
define(S_SAT, 'Sab');											//Defines abbreviation used for "Saturday"
define(S_ANONAME, 'An&#244;nimo');										//Defines what to print if there is no text entered in the name field
define(S_ANOTEXT, '');										//Defines what to print if there is no text entered in the comment field
define(S_ANOTITLE, '');									//Defines what to print if there is no text entered into subject field
define(S_RENZOKU, 'Erro: Flood detectado, post descartado.');						//Returns error for $sec/post spam filter
define(S_RENZOKU2, 'Erro: Flood detectado, arquivo descartado.');						//Returns error for $sec/upload spam filter
define(S_RENZOKU3, 'Erro: Flood detectado.');								//Returns error for flood? (don't know the specifics)
define(S_DUPE, 'Erro: Entrada duplicada de arquivo detectada.');						//Returns error for a duped file (same upload name or same tim/time)
define(S_NOTHREADERR, 'Erro: Thread especificada n&#227;o existe mais.');					//Returns error when a non-existant thread is accessed
define(S_SCRCHANGE, 'Atualizando p&#225;gina');									//Defines message to be displayed when post is successful	
define(S_TOODAMNSMALL, 'Erro: Imagem pequena demais');                //Error for small images								//
define(S_BADDELPASS, 'Erro: Senha incorreta.');							//Returns error for wrong password (when user tries to delete file)
define(S_WRONGPASS, 'Erro: Senha de administra&#231;&#227;o incorreta.');						//Returns error for wrong password (when trying to access Manager modes)
define(S_RETURNS, 'Retornar');										//Returns to HTML file instead of PHP--thus no log/SQLDB update occurs
define(S_LOGUPD, 'Atualizar');										//Updates the log/SQLDB by accessing the PHP file
define(S_MANAMODE, 'Modo de gerenciamento');									//Prints heading on top of Manager page
define(S_MANAREPDEL, 'Painel de gerenciador');								//Defines Management Panel radio button--allows the user to view the management panel (overview of all posts)
define(S_MANAPOST, 'Admin post');									//Defines Manager Post radio button--allows the user to post using HTML code in the comment box
define(S_MANASUB, 'Enviar');										//Defines name for submit button in Manager Mode
define(S_DELLIST, 'Painel de gerenciador');									//Prints sub-heading of Management Panel
define(S_ITDELETES, 'Deletar');										//Defines for deletion button in Management Panel
define(S_MDRESET, 'Resetar');										//Defines name for field reset button in Management Panel
define(S_MDONLYPIC, 'Arquivo apenas');									//Sets whether or not to delete only file, or entire post/thread
define(S_MDTABLE1, '<th>Deletar?</th><th>Post No.</th><th>Res</th><th>Hora</th><th>T&#243;pico</th>');			//Explains field names for Management Panel (Delete?->Subject)
define(S_MDTABLE2, '<th>Nome</th><th>Coment&#225;rio</th><th>Host</th><th>Tamanho<br>(Bytes)</th><th>md5</th><th>Resposta #</th><th>Nome de arquivo local</th><th>Idade</th>');	//Explains names for Management Panel (Name->md5)
define(S_RESET, 'Resetar');										//Sets name for field reset button (global)
define(S_IMGSPACEUSAGE, 'Espa&#231;o usado :');						//Prints space used KB by the board under Management Panel
define(S_CANNOTWRITE, 'Error: N&#227;o foi possivel escrever no diret&#243;rio.<br>');						//Returns error when the script cannot write to the directory, this is used on initial setup--check your chmod (777)
define(S_NOTWRITE, 'Error: N&#227;o foi possivel escrever no diret&#243;rio.<br>');						//Returns error when the script cannot write to the directory, the chmod (777) is wrong
define(S_NOTREAD, 'Erro: N&#227;o foi possivel ler do diret&#243;rio.<br>');						//Returns error when the script cannot read from the directory, the chmod (777) is wrong
define(S_NOTDIR, 'Erro: Diret&#243;rio n&#227;o existe.<br>');						//Returns error when the script cannot find/read from the directory (does not exist/isn't directory), the chmod (777) is wrong
define(S_SQLCONF, 'MySQL connection failure');		//MySQL connection failure
define(S_SQLDBSF, 'Database error, check SQL settings<br>');	//database select failure
define(S_TCREATE, 'Criando tabela!<br>\n');	//creating table
define(S_TCREATEF, 'N&#227;o foi possivel criar tabela!<br>');		//table creation failed
define(S_SQLFAIL, 'Problema SQL cr&#237;tico!<br>');		//SQL Failure
define(S_QUOTE, 'Cita&#231;&#227;o');
define(S_PERMALINK, 'Permalink morto');
define(S_RESNUM, 'Responder &#224; thread:');
define(S_BANS, 'Ban');
define(S_BANS_EXTRA, '');
define(S_CAPFAIL, 'Voc&#234; parece ter errado a verifica&#231;&#227;o.');
define(S_THREADLOCKED, 'Responder proibido');
?>
