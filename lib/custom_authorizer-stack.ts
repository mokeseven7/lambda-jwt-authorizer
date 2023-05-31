import * as cdk from 'aws-cdk-lib';
import { Construct } from 'constructs';
import * as ecr from 'aws-cdk-lib/aws-ecr';
import * as phpbuilder from '@mikemcgrath/phpbuilder';
import * as process from 'process';
import * as path from 'path';


export class CustomAuthorizerStack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    const repository = new ecr.Repository(this, "phpbuilder", {
      repositoryName: "phpbuilder"
    });

    const source = phpbuilder.PHPDockerImageSource.directory(path.join(process.cwd()));
    const destination = phpbuilder.PHPDockerImageDestination.ecr(repository, {tag: 'latest'});

    const image = new phpbuilder.PHPDockerImageBuilder(this, 'PHPBuilderStack', {
      source,
      destination
    })
  }
}
