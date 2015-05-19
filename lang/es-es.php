<<<<<<< HEAD
<?php
define(S_HOME, 'Inicio');											//Forwards to home page
define(S_ADMIN, 'Administracion');										//Forwards to Management Panel
define(S_RETURN, 'Regresar');										//Returns to image board
define(S_POSTING, 'Fijacion de modo: Respuesta');								//Prints message in red bar atop the reply screen
define(S_NOTAGS, 'Las etiquetas de HTML son permitidas.');								//Prints message on Management Board
define(S_NAME, 'Nombre');											//Describes name field
define(S_EMAIL, 'E-mail');										//Describes e-mail field
define(S_SUBJECT, 'Sujeto');										//Describes subject field
define(S_SUBMIT, 'Enviar');										//Describes submit button
define(S_COMMENT, 'Comentario');										//Describes comment field
define(S_UPLOADFILE, 'Archivo');										//Describes file field
define(S_NOFILE, 'Ningun Archivo');										//Describes file/no file checkbox
define(S_DELPASS, 'Contrasena');										//Describes password field
define(S_DELEXPL, '(Contrasena utilizada para la eliminacion de archivos)');							//Prints explanation for password box (to the right)
define(S_RULES, '<ul><li>Los tipos de archivo apoyados son: GIF, JPG, PNG</li>
<li>El tamano de archivo maximo permitido es '.MAX_KB.' KB.</li>
<li>Imagenes mayores que '.MAX_W.'x'.MAX_H.' pixeles seran reducidas.</li>
<li>Imagenes mas pequeno que '.MIN_W.'x'.MIN_H.' pixeles seran rechazado.</li>
</ul>');				//Prints rules under posting section
define(S_REPORTERR, 'Error: no Puede encontrar la respuesta.');							//Returns error when a reply (res) cannot be found
define(S_THUMB, 'Una del pulgar mostrada, imagen de chasquido para tamano natural.');					//Prints instructions for viewing real source
define(S_PICNAME, 'Archivo : ');										//Prints text before upload name/link
define(S_REPLY, 'Responder');										//Prints text for reply link
define(S_OLD, 'Marcado para eliminación (vieja).');								//Prints text to be displayed before post is marked for deletion, see: retention
define(S_RESU, '');											//Prints post?
define(S_ABBR, ' postes omitidos. Respuesta de Chasquido para ver.');						//Prints text to be shown when replies are hidden
define(S_REPDEL, 'Suprima Poste ');									//Prints text next to S_DELPICONLY (left)
define(S_DELPICONLY, 'Archivo Solo');									//Prints text next to checkbox for file deletion (right)
define(S_DELKEY, 'Contrasena ');										//Prints text next to password field for deletion (left)
define(S_DELETE, 'Suprimir');										//Defines deletion button's name
define(S_PREV, 'Anterior');										//Defines previous button
define(S_FIRSTPG, 'Anterior');										//Defines previous button
define(S_NEXT, 'Despues');											//Defines next button
define(S_LASTPG, 'Despues');										//Defines next button
define(S_FOOT, '- <a href="http://www.2chan.net/" target="_blank">futaba</a> + <a href="http://1chan.net/futallaby/" target="_blank">futallaby</a> + <a href="http://saguaroimgboard.tk/" target="_blank">saguaro 0.98.3b4</a> -'); //Prints footer (leave these credits)
define(S_RELOAD, 'Regresar');										//Reloads the image board (refresh)
define(S_UPFAIL, 'Error: Cargue fallado.');								//Returns error for failed upload (reason: unknown?)
define(S_NOREC, 'Error: no Puede encontrar el registro.');								//Returns error when record cannot be found
define(S_SAMEPIC, 'Error: Duplique la suma de control md5 descubierta.');						//Returns error when a md5 checksum dupe is detected
define(S_TOOBIG, '¡Esta imagen es demasiado grande! ¡Cargue algo mas pequeno!');
define(S_TOOBIGORNONE, 'Esta imagen es demasiado grande o no hay ninguna imagen en absoluto.');
define(S_UPGOOD, ' ¡'.$upfile_name.' cargado!<br><br>');					//Defines message to be displayed when file is successfully uploaded
define(S_STRREF, 'Error: la Cuerda se nego.');								//Returns error when a string is refused
define(S_UNJUST, 'Error: POSTE injusto.');								//Returns error on an unjust POST - prevents floodbots or ways not using POST method?
define(S_NOPIC, 'Error: Ningun archivo seleccionado.');								//Returns error for no file selected and override unchecked
define(S_NOTEXT, 'Error: Ningun texto entro.');								//Returns error for no text entered in to subject/comment
define(S_MANAGEMENT, 'Gerente : ');									//Defines prefix for Manager Post name
define(S_DELETION, 'Eliminación');										//Prints deletion message with quotes?
define(S_TOOLONG, 'Error: Campo demasiado mucho tiempo.');								//Returns error for too many characters in a given field
define(S_UNUSUAL, 'Error: respuesta anormal.');								//Returns error for abnormal reply? (this is a mystery!)
define(S_BADHOST, 'Error: el Anfitrión es prohibido.');								//Returns error for banned host ($badip string)
define(S_PROXY80, 'Error: Poder descubierto en:80.');							//Returns error for proxy detection on port 80
define(S_PROXY8080, 'Error: Poder descubierto en:8080.');							//Returns error for proxy detection on port 8080
define(S_SUN, 'Dom');											//Defines abbreviation used for "Sunday"
define(S_MON, 'Lun');											//Defines abbreviation used for "Monday"
define(S_TUE, 'Mar');											//Defines abbreviation used for "Tuesday"
define(S_WED, 'Mie');											//Defines abbreviation used for "Wednesday"
define(S_THU, 'Jue');											//Defines abbreviation used for "Thursday"
define(S_FRI, 'Vie');											//Defines abbreviation used for "Friday"
define(S_SAT, 'Sab');											//Defines abbreviation used for "Saturday"
define(S_ANONAME, 'Anonymous');										//Defines what to print if there is no text entered in the name field
define(S_ANOTEXT, '');										//Defines what to print if there is no text entered in the comment field
define(S_ANOTITLE, '');									//Defines what to print if there is no text entered into subject field
define(S_RENZOKU, 'Error: Inundacion descubierta, poste desechado.');						//Returns error for $sec/post spam filter
define(S_RENZOKU2, 'Error: Inundacion descubierta, archivo desechado.');						//Returns error for $sec/upload spam filter
define(S_RENZOKU3, 'Error: Inundacion descubierta.');								//Returns error for flood? (don't know the specifics)
define(S_DUPE, 'Error: entrada de archivo duplicada descubierta.');						//Returns error for a duped file (same upload name or same tim/time)
define(S_NOTHREADERR, 'Error: el Hilo especificado no existe.');					//Returns error when a non-existant thread is accessed
define(S_SCRCHANGE, 'Actualizacion de pagina.');									//Defines message to be displayed when post is successful	
define(S_TOODAMNSMALL, 'Error: Imagen demasiado pequena.');                //Error for small images									//
define(S_BADDELPASS, 'Error: Contraseña incorrecta.');							//Returns error for wrong password (when user tries to delete file)
define(S_WRONGPASS, 'Error: contraseña de dirección incorrecta.');						//Returns error for wrong password (when trying to access Manager modes)
define(S_RETURNS, 'Regresar');										//Returns to HTML file instead of PHP--thus no log/SQLDB update occurs
define(S_LOGUPD, 'Actualizacion');										//Updates the log/SQLDB by accessing the PHP file
define(S_MANAMODE, 'Modo de Gerente');									//Prints heading on top of Manager page
define(S_MANAREPDEL, 'Panel de Direccion');								//Defines Management Panel radio button--allows the user to view the management panel (overview of all posts)
define(S_MANAPOST, 'Poste de Gerente');									//Defines Manager Post radio button--allows the user to post using HTML code in the comment box
define(S_MANASUB, 'Enviar');										//Defines name for submit button in Manager Mode
define(S_DELLIST, 'Panel de administracion');									//Prints sub-heading of Management Panel
define(S_ITDELETES, 'Suprimir');										//Defines for deletion button in Management Panel
define(S_MDRESET, 'Reinicializado');										//Defines name for field reset button in Management Panel
define(S_MDONLYPIC, 'Archivo Sólo');									//Sets whether or not to delete only file, or entire post/thread
define(S_MDTABLE1, '<th>Suprimir?</th><th>Poste No.</th><th>Tiempo</th><th>Sujeto</th>');			//Explains field names for Management Panel (Delete?->Subject)
define(S_MDTABLE2, '<th>Nombre</th><th>Comentario</th><th>Anfitrion</th><th>Tamano<br>(Bytes)</th><th>md5</th><th>Respuesta #</th><th>Nombre del archivo Local</th><th>Edad</th>');	//Explains names for Management Panel (Name->md5)
define(S_RESET, 'Reinicializado');										//Sets name for field reset button (global)
define(S_IMGSPACEUSAGE, 'Espacio usado :');						//Prints space used KB by the board under Management Panel
define(S_CANNOTWRITE, 'Error: no Puede escribir al directorio.<br>');						//Returns error when the script cannot write to the directory, this is used on initial setup--check your chmod (777)
define(S_NOTWRITE, 'Error: no Puede escribir al directorio.<br>');						//Returns error when the script cannot write to the directory, the chmod (777) is wrong
define(S_NOTREAD, 'Error: no Puede leer del directorio.<br>');						//Returns error when the script cannot read from the directory, the chmod (777) is wrong
define(S_NOTDIR, 'Error: el Directorio no existe.<br>');						//Returns error when the script cannot find/read from the directory (does not exist/isn't directory), the chmod (777) is wrong
define(S_SQLCONF, 'Fracaso de unión de MySQL');		//MySQL connection failure
define(S_SQLDBSF, 'Error de base de datos, compruebe ajustes SQL<br>');	//database select failure
define(S_TCREATE, '¡Creacion de mesa!<br>\n');	//creating table
define(S_TCREATEF, '¡Incapaz de crear mesa!<br>');		//table creation failed
define(S_SQLFAIL, '¡Problema SQL crítico!<br>');		//SQL Failure
define(S_QUOTE, 'Cita');
define(S_PERMALINK, 'Permalink hilo');
define(S_RESNUM, 'Respuesta para enhebrar:');
define(S_BANS, 'Prohibicion');
define(S_BANS_EXTRA, '');
define(S_CAPFAIL, 'Usted parece tener mistyped la verificación.');
define(S_THREADLOCKED, 'Responder prohibido');
=======
<?
$S_HOME = 'Inicio';											//Forwards to home page
$S_ADMIN = 'Administracion';										//Forwards to Management Panel
$S_RETURN = 'Regresar';										//Returns to image board
$S_POSTING = 'Fijacion de modo: Respuesta';								//Prints message in red bar atop the reply screen
$S_NOTAGS = 'Las etiquetas de HTML son permitidas.';								//Prints message on Management Board
$S_NAME = 'Nombre';											//Describes name field
$S_EMAIL = 'E-mail';										//Describes e-mail field
$S_SUBJECT = 'Sujeto';										//Describes subject field
$S_SUBMIT = 'Enviar';										//Describes submit button
$S_COMMENT = 'Comentario';										//Describes comment field
$S_UPLOADFILE = 'Archivo';										//Describes file field
$S_NOFILE = 'Ningun Archivo';										//Describes file/no file checkbox
$S_DELPASS = 'Contrasena';										//Describes password field
$S_DELEXPL = '(Contrasena utilizada para la eliminacion de archivos)';							//Prints explanation for password box (to the right)
$S_RULES = '<ul><li>Los tipos de archivo apoyados son: GIF, JPG, PNG</li>
<li>El tamano de archivo maximo permitido es '.MAX_KB.' KB.</li>
<li>Imagenes mayores que '.MAX_W.'x'.MAX_H.' pixeles seran reducidas.</li>
<li>Imagenes mas pequeno que '.MIN_W.'x'.MIN_H.' pixeles seran rechazado.</li>
</ul>'				//Prints rules under posting section
$S_REPORTERR = 'Error: no Puede encontrar la respuesta.';							//Returns error when a reply (res) cannot be found
$S_THUMB = 'Una del pulgar mostrada, imagen de chasquido para tamano natural.';					//Prints instructions for viewing real source
$S_PICNAME = 'Archivo : ';										//Prints text before upload name/link
$S_REPLY = 'Responder';										//Prints text for reply link
$S_OLD = 'Marcado para eliminación (vieja).';								//Prints text to be displayed before post is marked for deletion, see: retention
$S_RESU = '';											//Prints post?
$S_ABBR = ' postes omitidos. Respuesta de Chasquido para ver.';						//Prints text to be shown when replies are hidden
$S_REPDEL = 'Suprima Poste ';									//Prints text next to S_DELPICONLY (left)
$S_DELPICONLY = 'Archivo Solo';									//Prints text next to checkbox for file deletion (right)
$S_DELKEY = 'Contrasena ';										//Prints text next to password field for deletion (left)
$S_DELETE = 'Suprimir';										//Defines deletion button's name
$S_PREV = 'Anterior';										//Defines previous button
$S_FIRSTPG = 'Anterior';										//Defines previous button
$S_NEXT = 'Despues';											//Defines next button
$S_LASTPG = 'Despues';										//Defines next button
$S_FOOT = '- <a href="http://www.2chan.net/" target="_blank">futaba</a> + <a href="http://1chan.net/futallaby/" target="_blank">futallaby</a> + <a href="http://saguaroimgboard.tk/" target="_blank">saguaro 0.98.3b4</a> -'; //Prints footer (leave these credits)
$S_RELOAD = 'Regresar';										//Reloads the image board (refresh)
$S_UPFAIL = 'Error: Cargue fallado.';								//Returns error for failed upload (reason: unknown?)
$S_NOREC = 'Error: no Puede encontrar el registro.';								//Returns error when record cannot be found
$S_SAMEPIC = 'Error: Duplique la suma de control md5 descubierta.';						//Returns error when a md5 checksum dupe is detected
$S_TOOBIG = '¡Esta imagen es demasiado grande! ¡Cargue algo mas pequeno!';
$S_TOOBIGORNONE = 'Esta imagen es demasiado grande o no hay ninguna imagen en absoluto.';
$S_UPGOOD = ' ¡'.$upfile_name.' cargado!<br><br>';					//Defines message to be displayed when file is successfully uploaded
$S_STRREF = 'Error: la Cuerda se nego.';								//Returns error when a string is refused
$S_UNJUST = 'Error: POSTE injusto.';								//Returns error on an unjust POST - prevents floodbots or ways not using POST method?
$S_NOPIC = 'Error: Ningun archivo seleccionado.';								//Returns error for no file selected and override unchecked
$S_NOTEXT = 'Error: Ningun texto entro.';								//Returns error for no text entered in to subject/comment
$S_MANAGEMENT = 'Gerente : ';									//Defines prefix for Manager Post name
$S_DELETION = 'Eliminación';										//Prints deletion message with quotes?
$S_TOOLONG = 'Error: Campo demasiado mucho tiempo.';								//Returns error for too many characters in a given field
$S_UNUSUAL = 'Error: respuesta anormal.';								//Returns error for abnormal reply? (this is a mystery!)
$S_BADHOST = 'Error: el Anfitrión es prohibido.';								//Returns error for banned host ($badip string)
$S_PROXY80 = 'Error: Poder descubierto en:80.';							//Returns error for proxy detection on port 80
$S_PROXY8080 = 'Error: Poder descubierto en:8080.';							//Returns error for proxy detection on port 8080
$S_SUN = 'Dom';											//Defines abbreviation used for "Sunday"
$S_MON = 'Lun';											//Defines abbreviation used for "Monday"
$S_TUE = 'Mar';											//Defines abbreviation used for "Tuesday"
$S_WED = 'Mie';											//Defines abbreviation used for "Wednesday"
$S_THU = 'Jue';											//Defines abbreviation used for "Thursday"
$S_FRI = 'Vie';											//Defines abbreviation used for "Friday"
$S_SAT = 'Sab';											//Defines abbreviation used for "Saturday"
$S_ANONAME = 'Anonymous';										//Defines what to print if there is no text entered in the name field
$S_ANOTEXT = '';										//Defines what to print if there is no text entered in the comment field
$S_ANOTITLE = '';									//Defines what to print if there is no text entered into subject field
$S_RENZOKU = 'Error: Inundacion descubierta, poste desechado.';						//Returns error for $sec/post spam filter
$S_RENZOKU2 = 'Error: Inundacion descubierta, archivo desechado.';						//Returns error for $sec/upload spam filter
$S_RENZOKU3 = 'Error: Inundacion descubierta.';								//Returns error for flood? (don't know the specifics)
$S_DUPE = 'Error: entrada de archivo duplicada descubierta.';						//Returns error for a duped file (same upload name or same tim/time)
$S_NOTHREADERR = 'Error: el Hilo especificado no existe.';					//Returns error when a non-existant thread is accessed
$S_SCRCHANGE = 'Actualizacion de pagina.';									//Defines message to be displayed when post is successful	
$S_TOODAMNSMALL = 'Error: Imagen demasiado pequena.';                //Error for small images									//
$S_BADDELPASS = 'Error: Contraseña incorrecta.';							//Returns error for wrong password (when user tries to delete file)
$S_WRONGPASS = 'Error: contraseña de dirección incorrecta.';						//Returns error for wrong password (when trying to access Manager modes)
$S_RETURNS = 'Regresar';										//Returns to HTML file instead of PHP--thus no log/SQLDB update occurs
$S_LOGUPD = 'Actualizacion';										//Updates the log/SQLDB by accessing the PHP file
$S_MANAMODE = 'Modo de Gerente';									//Prints heading on top of Manager page
$S_MANAREPDEL = 'Panel de Direccion';								//Defines Management Panel radio button--allows the user to view the management panel (overview of all posts)
$S_MANAPOST = 'Poste de Gerente';									//Defines Manager Post radio button--allows the user to post using HTML code in the comment box
$S_MANASUB = 'Enviar';										//Defines name for submit button in Manager Mode
$S_DELLIST = 'Panel de administracion';									//Prints sub-heading of Management Panel
$S_ITDELETES = 'Suprimir';										//Defines for deletion button in Management Panel
$S_MDRESET = 'Reinicializado';										//Defines name for field reset button in Management Panel
$S_MDONLYPIC = 'Archivo Sólo';									//Sets whether or not to delete only file, or entire post/thread
$S_MDTABLE1 = '<th>Suprimir?</th><th>Poste No.</th><th>Tiempo</th><th>Sujeto</th>';			//Explains field names for Management Panel (Delete?->Subject)
$S_MDTABLE2 = '<th>Nombre</th><th>Comentario</th><th>Anfitrion</th><th>Tamano<br>(Bytes)</th><th>md5</th><th>Respuesta #</th><th>Nombre del archivo Local</th><th>Edad</th>';	//Explains names for Management Panel (Name->md5)
$S_RESET = 'Reinicializado';										//Sets name for field reset button (global)
$S_IMGSPACEUSAGE = 'Espacio usado :';						//Prints space used KB by the board under Management Panel
$S_CANNOTWRITE = 'Error: no Puede escribir al directorio.<br>';						//Returns error when the script cannot write to the directory, this is used on initial setup--check your chmod (777)
$S_NOTWRITE = 'Error: no Puede escribir al directorio.<br>';						//Returns error when the script cannot write to the directory, the chmod (777) is wrong
$S_NOTREAD = 'Error: no Puede leer del directorio.<br>';						//Returns error when the script cannot read from the directory, the chmod (777) is wrong
$S_NOTDIR = 'Error: el Directorio no existe.<br>';						//Returns error when the script cannot find/read from the directory (does not exist/isn't directory), the chmod (777) is wrong
$S_SQLCONF = 'Fracaso de unión de MySQL';		//MySQL connection failure
$S_SQLDBSF = 'Error de base de datos, compruebe ajustes SQL<br>';	//database select failure
$S_TCREATE = '¡Creacion de mesa!<br>\n';	//creating table
$S_TCREATEF = '¡Incapaz de crear mesa!<br>';		//table creation failed
$S_SQLFAIL = '¡Problema SQL crítico!<br>';		//SQL Failure
$S_QUOTE = 'Cita';
$S_PERMALINK = 'Permalink hilo';
$S_RESNUM = 'Respuesta para enhebrar:';
$S_BANS = 'Prohibicion';
$S_BANS_EXTRA = '';
$S_CAPFAIL = 'Usted parece tener mistyped la verificación.';
>>>>>>> e95e46fd057fdc5e5ffd043bc34a6a5ce23672f2
?>
