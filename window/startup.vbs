' 쉘실행
' 이부분 뭔가 윈도우마다 다를 수 있음
Set sh = CreateObject("WScript.Shell")

' 뒤에 0 등은 안보이게 실행
' /c 는 cmd가 명령어 실행하고 창 닫기해줌
sh.Run "cmd /c wsl -u root -- mkdir -p /var/run/mysqld", 0, true
sh.Run "cmd /c wsl -u root -- chown mysql -R /var/run/mysqld", 0, true
sh.Run "cmd /c wsl -u root -- apachectl start", 0, true
sh.Run "cmd /c wsl -u root -- mysqld --user=root &", 0, true
