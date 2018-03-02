https://github.com/bbc/audiowaveform
mkdir /soft
cd /soft/
git clone https://github.com/bbc/audiowaveform.git
apt-get install clang
git clone https://github.com/google/googletest.git gtest
ln -s gtest/googlemock/ googlemock
ln -s gtest/googletest/ googletest
ln -s /usr/bin/clang /usr/local/bin/clang
ln -s /usr/bin/clang++  /usr/local/bin/clang++
cmake .
make
make install