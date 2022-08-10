echo utf8
chcp 65001

echo 프로그램기능 켜기: wsl, 가상머신

dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart

dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

echo 프로그램 기능 킨 다음 껏다켜야함

echo wsl 업데이트

wsl --update
wsl --shutdown

echo wsl 리눅스 설치
wsl --install -d  Ubuntu-20.04
wsl --set-default-version 2
wsl --set-version  Ubuntu-20.04 2

echo wsl 세팅 대기 20초

timeout 20 > NUL


echo 초기 스크립트 실행
pushd %~dp0
start /wait "" cmd /c cscript %~dp0startup.vbs


echo 아파치, 마리아디비 실행파일 시작프로그램에 복사
copy startup.vbs "C:\ProgramData\Microsoft\Windows\Start Menu\Programs\StartUp\startup.vbs"
