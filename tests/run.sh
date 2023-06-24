#!/usr/bin/env bash

method="$1"
shift

exitIfInvalidExitCode() {
    if [[ $1 -ne 0 ]]
    then
        exit $1
    fi
}

checkNetcat() {
    echo "Checking $3..."

    maxcounter=50
    counter=1

    while ! netcat -z $1 $2 ; do
        sleep 1
        counter=`expr $counter + 1`
        if [ $counter -gt $maxcounter ]; then
            >&2 echo "We have been waiting for $3 too long. Failing."
            exit 1
        fi;
    done

    echo "$3 is up"
}

checkMongoDB() {
    checkNetcat $MONGO_HOST $MONGO_PORT "MongoDB"
}

dependenciesUp() {
    echo "Dependencies up"
    checkMongoDB
}

dependenciesDown() {
    echo "Dependencies down"
}

generateCoverage() {
    php bin/phpcov merge build/coverage --html build/coverage/merged/html
    exitIfInvalidExitCode $?
}

unit() {
    dependenciesUp
    php bin/phpunit --testsuite=unit --no-coverage $*
    exitIfInvalidExitCode $?
}

unitCoverage() {
    dependenciesUp
    php bin/phpunit --testsuite=unit $*
    exitIfInvalidExitCode $?
}

testAll() {
    unit
}

testAllCoverage() {
    unitCoverage
    generateCoverage
}

case "$method" in
  all)
    testAll
    ;;
  unit)
    unit $*
    ;;
  all-coverage)
    testAllCoverage
    ;;
  unit-coverage)
    unitCoverage $*
    generateCoverage
    ;;
  *)
    testAll
esac

exit 0