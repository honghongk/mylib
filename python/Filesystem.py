import sys , os
import yaml, json
from python import Exception


if __name__ == '__main__' :
    raise Exception.Error(__file__+'은 단독으로 사용할 수 없습니다')

class Dir:
    @staticmethod
    def scan(dir):
        if not os.path.isdir(os.path.realpath(dir)):
            raise Exception.Error('없는 폴더입니다')
        return os.listdir(dir)

    @staticmethod
    def exist(dir):
        return True if os.path.isdir(dir) else False

    @staticmethod
    def touch(dir):
        return os.makedirs(dir,exist_ok=True)
class File:
    @staticmethod
    def mtime(file):
        return os.path.getmtime(file)

    @staticmethod
    def touch(file):
        return open(file,'w+',encoding="utf-8")

    @staticmethod
    def parse(file):
        ext = File.ext(file)
        with open(file , 'r' , encoding='utf8') as f:
            if ext == '.yaml':
                return yaml.load(f,Loader=yaml.SafeLoader)
            elif ext == '.yml':
                return yaml.load(f,Loader=yaml.SafeLoader)
            elif ext == '.json':
                return json.load(f)

    @staticmethod
    def ext(file:str):
        return list(os.path.splitext(file)).pop()
        #return file.split('.').pop()

    @staticmethod
    def exist(file):
        return True if os.path.isfile(file) else False

    @staticmethod
    def size(file):
        return os.path.getsize(file)

    @staticmethod
    def log(file, text:str):
        with open(file, 'a', encoding='utf-8') as f:
            f.write(text + "\n")
            f.close()